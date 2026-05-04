<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;


Route::get('/ping', function () {
  return response()->json([
      'message' => 'API OK',
      'time' => now()
  ]);
});

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/posts/{id}/comments', [CommentController::class, 'index']);
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

    Route::post('/posts/{id}/like', [LikeController::class, 'toggle']);
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    Route::delete('/posts/{id}/comments/{commentId}', [CommentController::class, 'destroy']);

    Route::get('/user/ads', [AdController::class, 'indexUser']);
    Route::post('/user/ads', [AdController::class, 'store']);
    Route::post('/user/ads/{id}', [AdController::class, 'update']);
    Route::delete('/user/ads/{id}', [AdController::class, 'destroy']);
});
