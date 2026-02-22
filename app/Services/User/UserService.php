<?php

namespace App\Services\User;

use App\DTOs\User\UserData;
use App\Repositories\User\UserRepositoryInterface;
use Domain\User\Entities\UserEntity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class UserService implements UserServiceInterface
{
    private const CACHE_KEY = 'users_list';

    public function __construct(private readonly UserRepositoryInterface $repository) {}

    public function listUsers(): Collection
    {
        $ttl = config('cache.ttl.users', 300);

        return Cache::remember(self::CACHE_KEY, $ttl, function (): Collection {
            return $this->repository
                ->getAll()
                ->map(static fn (UserEntity $entity): UserData => UserData::fromEntity($entity));
        });
    }
}
