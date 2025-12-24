<?php

namespace App\Repositories\User;

use Domain\User\Entities\UserEntity;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    /**
     * @return Collection<int, UserEntity>
     */
    public function getAll(): Collection;
}
