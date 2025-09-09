<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiGenerationPageController;
use App\Http\Controllers\VectorSearchPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/test', function () {
    return response('Laravel is working! ' . now());
})->middleware([]);
Route::get('/', [GalleryController::class, 'index'])->name('home');
Route::get('/gallery', [GalleryController::class, 'gallery'])->name('gallery');
Route::get('/albums', [AlbumController::class, 'index'])->name('albums.index');
Route::get('/albums/{album:slug}', [AlbumController::class, 'show'])->name('albums.show');
Route::get('/images/{image:slug}', [ImageController::class, 'show'])->name('images.show');
Route::get('/images/{image:slug}/download', [ImageController::class, 'download'])->name('images.download');

// Search routes
Route::get('/search', [GalleryController::class, 'search'])->name('search');

// Guest auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Image management
    Route::get('/my-images', [ImageController::class, 'myImages'])->name('images.mine');
    Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('/images', [ImageController::class, 'store'])->name('images.store');
    Route::get('/images/{image:slug}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('/images/{image:slug}', [ImageController::class, 'update'])->name('images.update');
    Route::delete('/images/{image:slug}', [ImageController::class, 'destroy'])->name('images.destroy');

    // Album management
    Route::get('/my-albums', [AlbumController::class, 'myAlbums'])->name('albums.mine');
    Route::get('/albums/create', [AlbumController::class, 'create'])->name('albums.create');
    Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::get('/albums/{album:slug}/edit', [AlbumController::class, 'edit'])->name('albums.edit');
    Route::patch('/albums/{album:slug}', [AlbumController::class, 'update'])->name('albums.update');
    Route::delete('/albums/{album:slug}', [AlbumController::class, 'destroy'])->name('albums.destroy');

    // AI Generation
    Route::get('/ai-generation', [AiGenerationPageController::class, 'index'])->name('ai.generation');

    // Vector Search
    Route::get('/vector-search', [VectorSearchPageController::class, 'index'])->name('vector.search');
});

// Admin routes
Route::middleware(['auth', 'role:admin|editor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/images', [AdminController::class, 'images'])->name('images');
    Route::get('/comments', [AdminController::class, 'comments'])->name('comments');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
});
