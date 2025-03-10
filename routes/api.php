<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'posts',
    'middleware' => 'auth'
], function () {
    Route::post('', [PostController::class, 'store']);
    Route::get('', [PostController::class, 'index']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::put('/{post}', [PostController::class, 'update'])->middleware('can:update,post');
    Route::delete('/{post}', [PostController::class, 'destroy'])->middleware('can:delete,post');
});

Route::group([
    'prefix' => 'auth',
    'middleware' => 'auth'
], function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware('auth');
    Route::post('/login', [AuthController::class, 'login'])->withoutMiddleware('auth');
});
