<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

abstract class Controller
{
    /**
     * Helper standar buat response sukses.
     * Otomatis menyisipkan info instansi di setiap response.
     */
    protected function successResponse($data)
    {
        return response()->json([
            'success' => true,
            'instansi' => $this->infoInstansi(),
            'data' => $data,
        ]);
    }

    protected function errorResponse(string $message, int $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'instansi' => $this->infoInstansi(),
            'message' => $message,
        ], $statusCode);
    }


    private function infoInstansi(): array
    {
        return Cache::remember('info_instansi', now()->addHour(), function () {
            $setting = Setting::select('nama_instansi', 'kode_ppkkemenkes')->first();

            return [
                'nama_instansi' => $setting->nama_instansi ?? null,
                'kode_ppk_kemenkes' => $setting->kode_ppkkemenkes ?? null,
            ];
        });
    }
}