<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KunjunganController;
use App\Http\Controllers\Api\PenyakitController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware('api.key')->group(function () {
    // Ralan
    Route::get('/kunjungan-ralan', [KunjunganController::class, 'jumlahRalan']);
    Route::get('/kunjungan-ralan/per-pj', [KunjunganController::class, 'jumlahRalanPerPj']);
    Route::get('/kunjungan-ralan/per-tanggal', [KunjunganController::class, 'jumlahRalanByDate']);

    // Ranap
    Route::get('/kunjungan-ranap', [KunjunganController::class, 'jumlahRanap']);
    Route::get('/kunjungan-ranap/per-pj', [KunjunganController::class, 'jumlahRanapPerPj']);

    // Penyakit
    Route::get('/penyakit-ralan/top10-ralan', [PenyakitController::class, 'top10Ralan']);
    Route::get('/penyakit-ralan/top10-ranap', [PenyakitController::class, 'top10Ranap']);
    Route::get('/penyakit/kanker', [PenyakitController::class, 'kanker']);
    Route::get('/penyakit/jantung', [PenyakitController::class, 'jantung']);
    Route::get('/penyakit/stroke', [PenyakitController::class, 'stroke']);
    Route::get('/penyakit/uronefro', [PenyakitController::class, 'uronefro']);
});
