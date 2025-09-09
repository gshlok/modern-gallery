<?php

use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\AiGenerationController;
use App\Http\Controllers\Api\VectorSearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes
Route::prefix('v1')->group(function () {
    // Images
    Route::get('/images', [ImageController::class, 'index']);
    Route::get('/images/{image:slug}', [ImageController::class, 'show']);
    Route::post('/images/{image:slug}/view', [ImageController::class, 'trackView']);

    // Albums
    Route::get('/albums', [AlbumController::class, 'index']);
    Route::get('/albums/{album:slug}', [AlbumController::class, 'show']);

    // Search
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);

    // Comments (public read)
    Route::get('/images/{image:slug}/comments', [CommentController::class, 'index']);
});

// Authenticated API routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Image management
    Route::post('/images', [ImageController::class, 'store']);
    Route::patch('/images/{image:slug}', [ImageController::class, 'update']);
    Route::delete('/images/{image:slug}', [ImageController::class, 'destroy']);
    Route::post('/images/batch-upload', [ImageController::class, 'batchUpload']);
    Route::post('/images/{image:slug}/download', [ImageController::class, 'trackDownload']);

    // Album management
    Route::post('/albums', [AlbumController::class, 'store']);
    Route::patch('/albums/{album:slug}', [AlbumController::class, 'update']);
    Route::delete('/albums/{album:slug}', [AlbumController::class, 'destroy']);
    Route::post('/albums/{album:slug}/images', [AlbumController::class, 'addImages']);
    Route::delete('/albums/{album:slug}/images/{image:slug}', [AlbumController::class, 'removeImage']);

    // Likes
    Route::post('/images/{image:slug}/like', [LikeController::class, 'likeImage']);
    Route::delete('/images/{image:slug}/like', [LikeController::class, 'unlikeImage']);
    Route::post('/albums/{album:slug}/like', [LikeController::class, 'likeAlbum']);
    Route::delete('/albums/{album:slug}/like', [LikeController::class, 'unlikeAlbum']);

    // Comments
    Route::post('/images/{image:slug}/comments', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/like', [LikeController::class, 'likeComment']);
    Route::delete('/comments/{comment}/like', [LikeController::class, 'unlikeComment']);
});

// Admin API routes
Route::middleware(['auth:sanctum', 'role:admin|editor'])->prefix('v1/admin')->group(function () {
    // Comment moderation
    Route::patch('/comments/{comment}/approve', [CommentController::class, 'approve']);
    Route::patch('/comments/{comment}/reject', [CommentController::class, 'reject']);
    Route::patch('/comments/{comment}/spam', [CommentController::class, 'markAsSpam']);

    // Batch operations
    Route::post('/images/batch-delete', [ImageController::class, 'batchDelete']);
    Route::post('/images/batch-update', [ImageController::class, 'batchUpdate']);
});

// AI Generation API (placeholder endpoints)
Route::middleware(['auth:sanctum'])->prefix('v1/ai')->group(function () {
    Route::post('/generate', [AiGenerationController::class, 'generate']);
    Route::get('/generations', [AiGenerationController::class, 'index']);
    Route::get('/generations/{generation}', [AiGenerationController::class, 'show']);
    Route::delete('/generations/{generation}', [AiGenerationController::class, 'destroy']);
    Route::get('/providers', [AiGenerationController::class, 'providers']);
    Route::get('/stats', [AiGenerationController::class, 'stats']);
});

// Vector Search API (placeholder endpoints)
Route::middleware(['auth:sanctum'])->prefix('v1/vector')->group(function () {
    Route::post('/search', [VectorSearchController::class, 'search']);
    Route::get('/similar/{image:slug}', [VectorSearchController::class, 'similar']);
    Route::post('/embeddings/generate/{image:slug}', [VectorSearchController::class, 'generateEmbedding']);
    Route::post('/embeddings/batch', [VectorSearchController::class, 'batchGenerateEmbeddings']);
    Route::get('/embeddings/status', [VectorSearchController::class, 'embeddingStatus']);
    Route::get('/stats', [VectorSearchController::class, 'stats']);
    Route::get('/health', [VectorSearchController::class, 'healthCheck']);
});
