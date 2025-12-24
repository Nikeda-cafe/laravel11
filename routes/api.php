<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('v1')->group(function (): void {
    Route::get('users', [UserController::class, 'index']);
});
