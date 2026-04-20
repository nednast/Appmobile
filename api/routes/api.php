<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdController;


Route::get('/ping', function () {
  return response()->json([
      'message' => 'API OK',
      'time' => now()
  ]);
});

Route::apiResource('posts', PostController::class);
Route::get('/ads', [AdController::class, 'index']);
Route::get('/ads/{id}', [AdController::class, 'show']);

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);

    // Posts du user connecté
    Route::get('/user/posts', [PostController::class, 'indexUser']);
    Route::get('/user/posts/{id}', [PostController::class, 'showUser']);
    Route::post('/user/posts', [PostController::class, 'store']);
    Route::post('/user/posts/{id}', [PostController::class, 'update']); 
    Route::delete('/user/posts/{id}', [PostController::class, 'destroy']);

    Route::get('/user/ads', [AdController::class, 'indexUser']);
    Route::post('/user/ads', [AdController::class, 'store']);
    Route::post('/user/ads/{id}', [AdController::class, 'update']);
    Route::delete('/user/ads/{id}', [AdController::class, 'destroy']);
});
