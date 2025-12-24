<?php

namespace App\Services\User;

use App\DTOs\User\UserData;
use App\Repositories\User\UserRepositoryInterface;
use Domain\User\Entities\UserEntity;
use Illuminate\Support\Collection;

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
