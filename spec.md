# Laravel 11 アプリケーション構築仕様書

## 1. 概要

本書は、Laravel 11 を用いた Web アプリケーションの構築仕様を定義する。  
ローカル環境は Docker（nginx, PHP/Laravel, MariaDB）で構築し、本番環境は AWS ECS 上で稼働する。  
品質担保としてテストコード、静的解析（Larastan）、コードフォーマッタ（Laravel Pint）を導入し、  
GitHub Actions による CI 自動化を行う。

アーキテクチャはレイヤードアーキテクチャを採用し、  
**Controller → Service → Repository** の流れで実装する。  
各レイヤのクラスは **interface で抽象化**し、依存関係逆転の原則（DIP）を満たす。

---

## 2. 技術スタック

### 2.1 フレームワーク / 言語

- PHP 8.2 以上
- Laravel 11.x
- nginx（ローカル環境）
- MariaDB 10.x（ローカル / 本番）

### 2.2 ライブラリ / ツール

- 静的解析: Larastan（nunomaduro/larastan）
- フォーマッタ: Laravel Pint
- テスト: Pest（推奨）／ PHPUnit（互換可）
- Composer
- Docker / docker-compose
- 本番環境: AWS ECS（+ ECR + ALB + RDS）
- CI: GitHub Actions

---

## 3. 環境構成

### 3.1 ローカル開発環境

#### 3.1.1 使用ツール

- Docker
- docker-compose v2

#### 3.1.2 コンテナ構成

| コンテナ | 内容 |
|---------|------|
| app     | PHP 8.2-fpm, Laravel, composer, PHP 拡張 |
| nginx   | Laravel の `public` を公開 |
| db      | MariaDB 10.x |

#### 3.1.3 docker-compose.yml 方針

- 共通ネットワーク `backend-network` を作成し、`app` / `nginx` / `db` を接続
- `.env` で DB 接続情報などを管理
- app コンテナ内で以下のコマンドを実行可能にする
  - `composer install`
  - `php artisan migrate`
  - `php artisan test` / `./vendor/bin/pest`
  - `./vendor/bin/phpstan analyse`
  - `./vendor/bin/pint`

---

### 3.2 本番環境（AWS ECS）

#### 3.2.1 構成概要

- ECS（Fargate または EC2 ベース）
- Docker イメージ格納: Amazon ECR
- DB: Amazon RDS for MariaDB
- ロードバランサ: ALB（Public Subnet）
- ECS サービス／タスク: Private Subnet
- 必要に応じて:
  - S3（ファイルストレージ）
  - ElastiCache for Redis（セッション／キャッシュ）

#### 3.2.2 デプロイフロー

1. GitHub Actions でアプリをビルドし Docker イメージ作成
2. ECR へ push
3. ECS タスク定義を更新
4. ECS サービスを更新しローリングデプロイ
5. マイグレーション実行
   - デプロイパイプライン内で `php artisan migrate --force`
   - またはマイグレーション用 ECS タスクを実行

---

## 4. ディレクトリ構成

Laravel 標準をベースにレイヤードアーキテクチャ用ディレクトリを追加する。

```text
app/
  Http/
    Controllers/
      └── Api/
          └── V1/
  Services/
    └── User/
        ├── UserServiceInterface.php
        └── UserService.php
  Repositories/
    └── User/
        ├── UserRepositoryInterface.php
        └── UserRepository.php
  DTOs/
  Models/
  Exceptions/

domain/              # 必要に応じてドメイン層を分離
  User/
    ├── Entities/
    └── ValueObjects/

tests/
  Feature/
  Unit/
