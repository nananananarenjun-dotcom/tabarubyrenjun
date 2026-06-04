<?php

use Illuminate\Support\Facades\Route;

// Kita biarkan rute awal kosong atau sekadar pesan bahwa API berjalan
Route::get('/', function () {
    return response()->json(['message' => 'Backend API Galeri Sabira Aktif!']);
});