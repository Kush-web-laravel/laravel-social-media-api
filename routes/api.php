<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LikeController;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::prefix('/user')->group(function(){
        Route::get('/', function (Request $request) {
            return $request->user();
        });
        Route::post('/setup-profile', [UserController::class, 'setupProfile'])->name('setupProfile');
    });
    Route::prefix('/post')->group(function(){

        Route::post('/create', [PostController::class, 'createPost'])->name('createPost');
        Route::get('/list', [PostController::class, 'listPosts'])->name('listPosts');
        Route::get('/{id}', [PostController::class, 'postDetails'])->name('postDetails');
        Route::post('update/{id}', [PostController::class, 'updatePost'])->name('updatePost');
        Route::post('/delete/{id}', [PostController::class, 'deletePost'])->name('deletePost');
        Route::post('/like', [LikeController::class, 'storeLikeData'])->name('likePost');
        Route::post('/unlike', [LikeController::class, 'unlikePost'])->name('unlikePost');
        
        Route::prefix('/comment')->group(function(){
            Route::post('/', [CommentController::class, 'storeData'])->name('storeComment');
            Route::post('/update', [CommentController::class, 'updateData'])->name('updateComment');
            Route::post('/delete', [CommentController::class, 'deleteData'])->name('deleteComment');
            Route::post('/reply', [CommentController::class, 'commentReply'])->name('replyComment');
        });
       
    });
    Route::post('/follow', [FollowController::class, 'follow'])->name('follow');
    Route::post('/unfollow', [FollowController::class, 'unfollow'])->name('unfollow');
    Route::post('/pending-requests', [FollowController::class, 'pendingRequests'])->name('pendingRequests');
    Route::post('/accept-request', [FollowController::class, 'acceptRequest'])->name('acceptRequest');
});