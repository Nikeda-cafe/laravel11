<?php

namespace App\DTOs\User;

use Domain\User\Entities\UserEntity;

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
