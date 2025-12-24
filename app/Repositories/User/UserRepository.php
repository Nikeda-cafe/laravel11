<?php

namespace App\Repositories\User;

use App\Models\User;
use Domain\User\Entities\UserEntity;
use Illuminate\Support\Collection;

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
