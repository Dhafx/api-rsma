<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    protected function resolveTanggal(Request $request): array
    {
        // kalau ada isinya, harus format tanggal yang valid (YYYY-MM-DD)
        $request->validate([
            'tanggal_awal' => 'nullable|date_format:Y-m-d',
            'tanggal_akhir' => 'nullable|date_format:Y-m-d|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.date_format' => 'Format tanggal_awal harus YYYY-MM-DD, contoh: 2026-08-05',
            'tanggal_akhir.date_format' => 'Format tanggal_akhir harus YYYY-MM-DD, contoh: 2026-09-04',
            'tanggal_akhir.after_or_equal' => 'tanggal_akhir tidak boleh sebelum tanggal_awal',
        ]);

        [$defaultAwal, $defaultAkhir] = $this->periodeDefault();

        $tanggalAwal = $request->query('tanggal_awal', $defaultAwal);
        $tanggalAkhir = $request->query('tanggal_akhir', $defaultAkhir);

        return [$tanggalAwal, $tanggalAkhir];
    }

    /**
     * Menghitung periode 1 bulan yang dimulai tanggal 5 (default kalau
     * tidak ada tanggal_awal/tanggal_akhir yang dikirim lewat URL).
     */
    private function periodeDefault(): array
    {
        $hariIni = Carbon::now();

        if ($hariIni->day >= 5) {
            $awal = $hariIni->copy()->day(5);
        } else {
            $awal = $hariIni->copy()->subMonthNoOverflow()->day(5);
        }

        $akhir = $awal->copy()->addMonthNoOverflow()->subDay();

        return [$awal->toDateString(), $akhir->toDateString()];
    }
}