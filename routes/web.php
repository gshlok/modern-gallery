<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/image/{uuid}', [GalleryController::class, 'show'])->name('gallery.show');
    Route::get('/upload', [GalleryController::class, 'upload'])->name('gallery.upload');
    Route::post('/upload', [GalleryController::class, 'store'])->name('gallery.store');
    Route::post('/image/{uuid}/rename', [GalleryController::class, 'rename'])->name('gallery.rename');
    Route::delete('/image/{uuid}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::get('/albums/create', [AlbumController::class, 'create'])->name('albums.create');
    Route::get('/albums', [AlbumController::class, 'index'])->name('albums.index');
    Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');
    Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::get('/albums/{album}/edit', [AlbumController::class, 'edit'])->name('albums.edit');
    Route::put('/albums/{album}', [AlbumController::class, 'update'])->name('albums.update');
    Route::delete('/albums/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');
    // Manage album images
    Route::post('/albums/{album}/images/{uuid}', [AlbumController::class, 'addImage'])->name('albums.images.add');
    Route::delete('/albums/{album}/images/{uuid}', [AlbumController::class, 'removeImage'])->name('albums.images.remove');
    Route::get('/ai/create', [AIController::class, 'create'])->name('ai.create');
    Route::post('/ai/generate', [AIController::class, 'generate'])->name('ai.generate');
    Route::get('/ai/history', [AIController::class, 'history'])->name('ai.history');
    Route::delete('/user/delete', [UserController::class, 'destroy'])->name('user.destroy');

    // Profile routes for Breeze views (delete-user-form references route('profile.destroy'))
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return redirect()->route('gallery.index');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
