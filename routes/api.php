<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Example API route (optional)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
