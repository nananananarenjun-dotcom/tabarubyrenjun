<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Backend API Galeri Sabira Aktif!']);
});

Route::get('/test-signature', function () {
    return response()->json([
        'app_url' => config('app.url'),
        'current_url' => request()->fullUrl(),
        'https' => request()->secure(),
        'host' => request()->getHost(),
    ]);
});