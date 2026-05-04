<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

$prefix = 'users';

Route::middleware([
    'auth',
])->group(function () use ($prefix) {
    Route::resource($prefix, UserController::class);

    Route::put('password-update/{id}', [UserController::class, 'updatePassword'])->name('users.password-update');
    Route::get('profile', [UserController::class, 'profile'])->name('user.profile');
    Route::get('profile-edit', [UserController::class, 'profileEdit'])->name('user.profile-edit');
});
