<?php

namespace Domain\User\Entities;

use Domain\User\ValueObjects\UserId;
use Illuminate\Support\Carbon; // for type hints

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

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function emailVerifiedAt(): ?Carbon
    {
        return $this->emailVerifiedAt;
    }

    /**
     * @return array{id:int,name:string,email:string,email_verified_at:?string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt?->toISOString(),
        ];
    }
}
