# アーキテクチャドキュメント

## 概要

このアプリケーションは、Laravel 11をベースとした**レイヤードアーキテクチャ**を採用しています。Controller → Service → Repository の3層構造に、DTO（Data Transfer Object）層とDomain層を組み合わせることで、関心の分離とテスタビリティを実現しています。

## アーキテクチャの全体像

```
┌─────────────────────────────────────────────────────────┐
│                    HTTP Request                         │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Controller Layer                           │
│  (HTTPリクエスト/レスポンスの処理)                      │
│  - UserController                                       │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ UserServiceInterface
                     ▼
┌─────────────────────────────────────────────────────────┐
│               Service Layer                             │
│  (ビジネスロジック)                                     │
│  - UserService                                          │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ UserRepositoryInterface
                     ▼
┌─────────────────────────────────────────────────────────┐
│            Repository Layer                             │
│  (データアクセス)                                       │
│  - UserRepository                                       │
└────────────────────┬────────────────────────────────────┘
                     │
                     │ Eloquent Model
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Database                                   │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│              Domain Layer                               │
│  (ドメインモデル)                                       │
│  - UserEntity (Entity)                                  │
│  - UserId (ValueObject)                                 │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│              DTO Layer                                  │
│  (データ転送オブジェクト)                                │
│  - UserData                                             │
└─────────────────────────────────────────────────────────┘
```

## レイヤーの詳細

### 1. Controller Layer（コントローラー層）

**役割**: HTTPリクエストの受信とレスポンスの生成

**場所**: `app/Http/Controllers/`

**特徴**:
- Service層のインターフェースにのみ依存
- HTTP関連の処理（リクエストバリデーション、レスポンス形式など）のみを担当
- ビジネスロジックは含まない

**例**:
```12:21:app/Http/Controllers/Api/V1/UserController.php
    public function __construct(private readonly UserServiceInterface $userService) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->listUsers()->map->toArray();

        return response()->json([
            'data' => $users,
        ]);
    }
```

### 2. Service Layer（サービス層）

**役割**: ビジネスロジックの実装

**場所**: `app/Services/`

**特徴**:
- Repository層のインターフェースに依存
- ビジネスロジックを実装
- Domain EntityをDTOに変換する責務を持つ
- インターフェースと実装を分離（依存性逆転の原則）

**例**:
```10:20:app/Services/User/UserService.php
final class UserService implements UserServiceInterface
{
    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function listUsers(): Collection
    {
        return $this->repository
            ->getAll()
            ->map(static fn (UserEntity $entity): UserData => UserData::fromEntity($entity));
    }
}
```

### 3. Repository Layer（リポジトリ層）

**役割**: データアクセスの抽象化

**場所**: `app/Repositories/`

**特徴**:
- Eloquent Modelを使用してデータベースにアクセス
- Domain Entityを返却する
- インターフェースと実装を分離（テスト容易性の向上）

**例**:
```9:26:app/Repositories/User/UserRepository.php
final class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly User $model) {}

    public function getAll(): Collection
    {
        return $this->model
            ->newQuery()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user): UserEntity => UserEntity::fromPrimitives(
                id: (int) $user->getAttribute('id'),
                name: (string) $user->getAttribute('name'),
                email: (string) $user->getAttribute('email'),
                emailVerifiedAt: $user->getAttribute('email_verified_at')?->toISOString(),
            ));
    }
}
```

### 4. Domain Layer（ドメイン層）

**役割**: ビジネスルールとドメインモデルの表現

**場所**: `domain/`

**特徴**:
- Entity: ドメインの主要な概念を表現
- ValueObject: 不変の値オブジェクト（バリデーションロジックを含む）
- ビジネスルールをコードで表現

**Entity例**:
```8:25:domain/User/Entities/UserEntity.php
final class UserEntity
{
    public function __construct(
        private readonly UserId $id,
        private readonly string $name,
        private readonly string $email,
        private readonly ?Carbon $emailVerifiedAt = null,
    ) {}

    public static function fromPrimitives(int $id, string $name, string $email, ?string $emailVerifiedAt): self
    {
        return new self(
            id: new UserId($id),
            name: $name,
            email: $email,
            emailVerifiedAt: $emailVerifiedAt ? Carbon::parse($emailVerifiedAt) : null,
        );
    }
```

**ValueObject例**:
```7:14:domain/User/ValueObjects/UserId.php
final class UserId
{
    public function __construct(private readonly int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('User id must be positive.');
        }
    }
```

### 5. DTO Layer（DTO層）

**役割**: レイヤー間でのデータ転送

**場所**: `app/DTOs/`

**特徴**:
- APIレスポンスの形式を定義
- Domain EntityからDTOへの変換メソッドを提供
- 不変オブジェクト（readonlyプロパティ）

**例**:
```7:38:app/DTOs/User/UserData.php
final class UserData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $emailVerifiedAt,
    ) {}

    public static function fromEntity(UserEntity $entity): self
    {
        return new self(
            id: $entity->id()->value(),
            name: $entity->name(),
            email: $entity->email(),
            emailVerifiedAt: $entity->emailVerifiedAt()?->toISOString(),
        );
    }

    /**
     * @return array{id:int,name:string,email:string,email_verified_at:?string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt,
        ];
    }
}
```

## データフロー

### リクエスト処理の流れ

1. **HTTPリクエスト受信** (`UserController::index()`)
   - ルーティング: `GET /api/v1/users`

2. **Service呼び出し** (`UserService::listUsers()`)
   - ControllerがServiceインターフェースを呼び出し

3. **Repository呼び出し** (`UserRepository::getAll()`)
   - ServiceがRepositoryインターフェースを呼び出し

4. **データ取得** (`UserRepository`)
   - Eloquent Modelを使用してデータベースから取得
   - Domain Entity (`UserEntity`) に変換

5. **DTO変換** (`UserService`)
   - Domain EntityをDTO (`UserData`) に変換

6. **レスポンス生成** (`UserController`)
   - DTOを配列に変換してJSONレスポンスとして返却

```
HTTP Request
    ↓
Controller (UserController)
    ↓ (UserServiceInterface)
Service (UserService)
    ↓ (UserRepositoryInterface)
Repository (UserRepository)
    ↓ (Eloquent Model)
Database
    ↓
UserEntity (Domain)
    ↓
UserData (DTO)
    ↓
JSON Response
```

## 依存性注入（DI）

### ServiceProviderによる登録

インターフェースと実装のバインドは `RepositoryServiceProvider` で行います。

```13:17:app/Providers/RepositoryServiceProvider.php
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }
```

このプロバイダーは `bootstrap/providers.php` で登録されています。

```3:6:bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
];
```

## ディレクトリ構造

```
app/
├── Http/
│   └── Controllers/
│       ├── Controller.php
│       ├── Api/
│       │   └── V1/
│       │       └── UserController.php
│       └── UserController.php
├── Services/
│   └── User/
│       ├── UserService.php
│       └── UserServiceInterface.php
├── Repositories/
│   └── User/
│       ├── UserRepository.php
│       └── UserRepositoryInterface.php
├── DTOs/
│   └── User/
│       └── UserData.php
├── Models/
│   └── User.php
└── Providers/
    ├── AppServiceProvider.php
    └── RepositoryServiceProvider.php

domain/
└── User/
    ├── Entities/
    │   └── UserEntity.php
    └── ValueObjects/
        └── UserId.php
```

## 設計原則

### 1. 依存性逆転の原則（DIP）

- 上位層（Controller, Service）は下位層の実装ではなく、インターフェースに依存
- これにより、テスト時にモックを注入しやすくなる

### 2. 単一責任の原則（SRP）

- 各レイヤーは明確な責務を持つ
  - Controller: HTTP処理
  - Service: ビジネスロジック
  - Repository: データアクセス

### 3. 関心の分離（SoC）

- 各レイヤーは独立しており、変更の影響範囲が限定的

### 4. ドメイン駆動設計（DDD）の要素

- Entity: 識別子を持つドメインオブジェクト
- ValueObject: 不変の値オブジェクト（バリデーションロジックを含む）

## テスト戦略

### 各レイヤーのテスト方針

1. **Controller層**
   - Feature TestでHTTPリクエスト/レスポンスをテスト
   - Serviceをモック化可能

2. **Service層**
   - Unit Testでビジネスロジックをテスト
   - Repositoryをモック化

3. **Repository層**
   - Feature Testでデータベースアクセスをテスト
   - 実際のデータベースを使用（テスト用DB）

4. **Domain層**
   - Unit Testでビジネスルールをテスト
   - 外部依存なし

## 拡張性

### 新しい機能を追加する場合

1. **Domain層**
   - 必要に応じてEntity/ValueObjectを追加

2. **Repository層**
   - インターフェースと実装を追加
   - `RepositoryServiceProvider` でバインド

3. **Service層**
   - インターフェースと実装を追加
   - `RepositoryServiceProvider` でバインド

4. **DTO層**
   - 必要に応じてDTOを追加

5. **Controller層**
   - 新しいエンドポイントを追加

## ベストプラクティス

1. **インターフェースの使用**
   - ServiceとRepositoryは必ずインターフェースを定義
   - テスト容易性と拡張性を確保

2. **finalクラスの使用**
   - 実装クラスは `final` で定義し、継承を防ぐ

3. **型安全性**
   - すべてのメソッドに戻り値の型を明示
   - PHPDocでコレクションの型を指定

4. **不変性**
   - DTOとValueObjectは不変（readonly）として設計

5. **命名規則**
   - インターフェース: `{Name}Interface`
   - 実装: `{Name}`（インターフェース名からInterfaceを除いたもの）

## まとめ

このアーキテクチャにより、以下のメリットが得られます：

- **テスタビリティ**: 各レイヤーを独立してテスト可能
- **保守性**: 責務が明確で変更の影響範囲が限定的
- **拡張性**: インターフェースベースの設計により、実装の差し替えが容易
- **型安全性**: 厳密な型定義により、実行時エラーを削減
- **ドメインの表現力**: Entity/ValueObjectにより、ビジネスルールをコードで表現

