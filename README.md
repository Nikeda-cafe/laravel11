# Laravel 11 Layered Architecture Boilerplate

このリポジトリは spec.md の要件に沿って構築した Laravel 11 アプリケーションです。Docker 上で PHP-FPM / nginx / MariaDB を動かし、Controller → Service → Repository にドメイン層と DTO を組み合わせたレイヤードアーキテクチャで API を実装しています。Pest・Larastan・Laravel Pint を導入済みで、GitHub Actions による CI も用意しています。

## 技術スタック

- PHP 8.2 / Laravel 11.47
- Docker + docker-compose（app + nginx + MariaDB）
- Pest, Laravel Pint, Larastan
- GitHub Actions (tests + pint + phpstan)

## ディレクトリ/アーキテクチャ

```
app/
  Http/Controllers/Api/V1/UserController.php
  Services/User/*
  Repositories/User/*
  DTOs/User/UserData.php
domain/
  User/Entities/UserEntity.php
  User/ValueObjects/UserId.php
```

- Controller 層は DTO を返却する Service にのみ依存します。
- Service 層は Repository interface に依存し、RepositoryRequest は Eloquent を用いてドメイン Entity を生成します。
- Domain 層では Entity / ValueObject でビジネスルールを表現しています。

## API

| Method | Path          | Description        |
| ------ | ------------- | ------------------ |
| GET    | `/api/v1/users` | ユーザー一覧 (DTO) |

レスポンス例：

```json
{
  "data": [
    {"id": 1, "name": "Test User", "email": "test@example.com", "email_verified_at": null}
  ]
}
```

## ローカル環境（Docker）

前提: Docker Desktop 等が起動していること。

```bash
cp .env.example .env
# 初回のみ依存ライブラリをインストール
docker compose run --rm app composer install
# APP_KEY を発行
docker compose run --rm app php artisan key:generate
# DB マイグレーション + 初期データ
docker compose run --rm app php artisan migrate --seed
# アプリ起動
docker compose up -d
```

- Web: http://localhost:8080
- MariaDB: localhost:3306 (`app` / `secret`)

### よく使うコマンド

```bash
# Pest
docker compose run --rm app ./vendor/bin/pest
# Laravel Pint (差分チェック)
docker compose run --rm app ./vendor/bin/pint --test
# Larastan
docker compose run --rm app ./vendor/bin/phpstan analyse
```

## CI

`.github/workflows/ci.yml` で以下を自動実行します。

1. Composer install
2. Laravel Pint (--test)
3. PHPStan (Larastan)
4. Pest

## AWS ECS へのデプロイ

このプロジェクトは AWS ECS (Fargate) で実行できるように設定されています。

### 前提条件

- AWS CLI がインストールされ、設定されていること
- Docker がインストールされていること
- ECR へのアクセス権限があること

### GitHub Actions による自動デプロイ（ECR）

`.github/workflows/deploy-ecr.yml` により、`main` または `develop` ブランチへのpush時に自動的にECRへイメージをビルド・プッシュします。

#### OIDC認証の設定（推奨）

このワークフローはOIDC（OpenID Connect）を使用してAWSに認証します。アクセスキーを保存する必要がなく、より安全です。

##### 1. AWS側の設定

**1-1. IAM Identity Providerの作成**

GitHubをOIDCプロバイダーとして登録します：

```bash
aws iam create-open-id-connect-provider \
  --url https://token.actions.githubusercontent.com \
  --client-id-list sts.amazonaws.com \
  --thumbprint-list 6938fd4d98bab03faadb97b34396831e3780aea1 \
  --region ap-northeast-1
```

**1-2. IAMロールの作成**

GitHub ActionsからAssumeRoleできるIAMロールを作成します：

```bash
# 信頼ポリシー（trust-policy.json）
cat > trust-policy.json <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Principal": {
        "Federated": "arn:aws:iam::YOUR_ACCOUNT_ID:oidc-provider/token.actions.githubusercontent.com"
      },
      "Action": "sts:AssumeRoleWithWebIdentity",
      "Condition": {
        "StringEquals": {
          "token.actions.githubusercontent.com:aud": "sts.amazonaws.com"
        },
        "StringLike": {
          "token.actions.githubusercontent.com:sub": "repo:YOUR_GITHUB_ORG/YOUR_REPO_NAME:*"
        }
      }
    }
  ]
}
EOF

# ロールの作成
aws iam create-role \
  --role-name GitHubActionsECRRole \
  --assume-role-policy-document file://trust-policy.json \
  --region ap-northeast-1
```

**1-3. IAMポリシーのアタッチ**

ECRとECSへのアクセス権限を持つポリシーをアタッチします：

```bash
# ポリシードキュメント（deploy-policy.json）
cat > deploy-policy.json <<EOF
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "ecr:GetAuthorizationToken",
        "ecr:BatchCheckLayerAvailability",
        "ecr:GetDownloadUrlForLayer",
        "ecr:BatchGetImage",
        "ecr:PutImage",
        "ecr:InitiateLayerUpload",
        "ecr:UploadLayerPart",
        "ecr:CompleteLayerUpload",
        "ecr:DescribeRepositories",
        "ecr:CreateRepository"
      ],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": [
        "ecs:DescribeTaskDefinition",
        "ecs:RegisterTaskDefinition",
        "ecs:UpdateService",
        "ecs:DescribeServices",
        "ecs:ListTasks",
        "ecs:DescribeTasks"
      ],
      "Resource": "*"
    },
    {
      "Effect": "Allow",
      "Action": [
        "iam:PassRole"
      ],
      "Resource": "*",
      "Condition": {
        "StringEquals": {
          "iam:PassedToService": "ecs-tasks.amazonaws.com"
        }
      }
    }
  ]
}
EOF

# ポリシーの作成とアタッチ
aws iam put-role-policy \
  --role-name GitHubActionsECRRole \
  --policy-name ECRECSPushPolicy \
  --policy-document file://deploy-policy.json \
  --region ap-northeast-1
```

**1-4. ロールARNの確認**

```bash
aws iam get-role --role-name GitHubActionsECRRole --query 'Role.Arn' --output text
```

##### 2. GitHub側の設定

GitHubリポジトリの **Settings → Secrets and variables → Actions** で以下のSecretを設定してください：

- `AWS_ROLE_ARN`: 上記で作成したIAMロールのARN（例: `arn:aws:iam::123456789012:role/GitHubActionsECRRole`）

**注意**: `YOUR_ACCOUNT_ID`、`YOUR_GITHUB_ORG`、`YOUR_REPO_NAME` は実際の値に置き換えてください。

#### ワークフローの動作

- **全ブランチ**: Git hashのshort形式（7文字、例: `a1b2c3d`）をタグとしてECRへpushし、ECSサービスをローリングアップデート
- **手動実行**: GitHub ActionsのUIから任意のタグを指定可能（未指定の場合はGit hashのshort形式を使用）

**デプロイフロー**:
1. ECRへイメージをビルド・プッシュ（Laravelアプリとnginx）
2. 既存のECSタスク定義を取得
3. 新しいイメージURIでタスク定義を更新
4. 新しいタスク定義を登録
5. ECSサービスを更新してローリングデプロイを実行
6. サービスが安定するまで待機

ECRリポジトリ名とECS設定は以下の通りです（`.github/workflows/deploy-ecr.yml` の `env` セクションで変更可能）：
- Laravelアプリ: `laravel-app`
- nginx: `laravel-nginx`
- ECSクラスター: `laravel-cluster`
- ECSサービス: `laravel-service`
- タスク定義ファミリー: `laravel-task`

### 手動デプロイ手順

#### 1. 環境変数の設定

`ecs-task-definition.json` を編集して、以下の値を設定してください：

- `YOUR_ACCOUNT_ID`: AWSアカウントID
- `YOUR_REGION`: AWSリージョン（例: `ap-northeast-1`）
- IAMロールARN（`executionRoleArn`, `taskRoleArn`）
- Secrets ManagerのARN（環境変数やシークレット）

#### 2. ECRへのイメージプッシュ

```bash
# 環境変数を設定（オプション）
export AWS_REGION=ap-northeast-1
export AWS_ACCOUNT_ID=123456789012
export IMAGE_TAG=latest

# ECRへのプッシュ
./scripts/push-to-ecr.sh
```

このスクリプトは以下を実行します：
- ECRリポジトリの作成（存在しない場合）
- Laravelイメージのビルドとプッシュ
- nginxイメージのビルドとプッシュ

#### 3. ECSクラスターとサービスの作成

初回デプロイ時は、AWSコンソールまたはAWS CLIで以下を作成する必要があります：

1. **ECSクラスターの作成**
   ```bash
   aws ecs create-cluster --cluster-name laravel-cluster --region ap-northeast-1
   ```

2. **CloudWatch Logsグループの作成**
   ```bash
   aws logs create-log-group --log-group-name /ecs/laravel-app --region ap-northeast-1
   ```

3. **ECSサービスの作成**
   - VPC、サブネット、セキュリティグループを設定
   - ロードバランサー（ALB）を設定（オプション）

#### 4. タスク定義の登録とデプロイ

```bash
# 環境変数を設定
export AWS_REGION=ap-northeast-1
export CLUSTER_NAME=laravel-cluster
export SERVICE_NAME=laravel-service

# デプロイ
./scripts/deploy-ecs.sh
```

### ファイル構成

- `Dockerfile`: Laravelコンテナ用（マルチステージビルド）
- `Dockerfile.nginx`: nginxコンテナ用
- `ecs-task-definition.json`: ECSタスク定義
- `docker/nginx/ecs.conf`: ECS用nginx設定
- `scripts/push-to-ecr.sh`: ECRへのプッシュスクリプト
- `scripts/deploy-ecs.sh`: ECSデプロイスクリプト

### 注意事項

- 環境変数（`APP_KEY`、DB接続情報など）は Secrets Manager で管理することを推奨します
- ストレージ（`storage`、`bootstrap/cache`）の永続化が必要な場合は EFS を使用してください
- セキュリティグループで nginx コンテナの 80 番ポートのみ公開してください

## その他

- `.env.testing` は in-memory SQLite を使うため、CI/ローカルテストも DB サーバー不要です。
- `spec.md` に沿ったインフラ構成や AWS ECS へのデプロイ用パイプラインを拡張しやすいディレクトリ分割になっています。
