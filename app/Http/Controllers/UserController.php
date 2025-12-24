<?php

namespace App\Http\Controllers;

use App\Services\User\UserServiceInterface;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserServiceInterface $userService) {}

    public function index(): View
    {
        $users = $this->userService->listUsers();

        return view('users.index', compact('users'));
    }
}
