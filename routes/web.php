<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/image/{uuid}', [GalleryController::class, 'show'])->name('gallery.show');

Route::middleware('auth')->group(function () {
    Route::get('/upload', [GalleryController::class, 'upload'])->name('gallery.upload');
    Route::post('/upload', [GalleryController::class, 'store'])->name('gallery.store');
    Route::post('/image/{uuid}/rename', [GalleryController::class, 'rename'])->name('gallery.rename');
    Route::delete('/image/{uuid}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::get('/albums/create', [AlbumController::class, 'create'])->name('albums.create');
    Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::get('/ai/create', [AIController::class, 'create'])->name('ai.create');
    Route::post('/ai/generate', [AIController::class, 'generate'])->name('ai.generate');
    Route::get('/ai/history', [AIController::class, 'history'])->name('ai.history');
    Route::delete('/user/delete', [UserController::class, 'destroy'])->name('user.destroy');

    Route::get('/dashboard', function () {
        return redirect()->route('gallery.index');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
