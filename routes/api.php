<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KunjunganController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Ralan
Route::get('/kunjungan-ralan', [KunjunganController::class, 'jumlahRalan']);
Route::get('/kunjungan-ralan/per-pj', [KunjunganController::class, 'jumlahRalanPerPj']);

// Ranap
Route::get('/kunjungan-ranap', [KunjunganController::class, 'jumlahRanap']);
Route::get('/kunjungan-ranap/per-pj', [KunjunganController::class, 'jumlahRanapPerPj']);
