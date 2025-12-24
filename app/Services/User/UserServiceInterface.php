<?php

namespace App\Services\User;

use App\DTOs\User\UserData;
use Illuminate\Support\Collection;

interface UserServiceInterface
{
    /**
     * @return Collection<int, UserData>
     */
    public function listUsers(): Collection;
}
