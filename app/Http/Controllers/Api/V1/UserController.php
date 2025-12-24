<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\User\UserServiceInterface;
use Illuminate\Http\JsonResponse;

final class UserController extends Controller
{
    public function __construct(private readonly UserServiceInterface $userService) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->listUsers()->map->toArray();

        return response()->json([
            'data' => $users,
        ]);
    }
}
