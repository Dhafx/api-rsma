<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenyakitController extends Controller
{
    public function top10Ralan(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir] = $this->periodeDefault();
        $tanggalAwal = $request->query('tanggal_awal', $tanggalAwal);
        $tanggalAkhir = $request->query('tanggal_akhir', $tanggalAkhir);

        try {
            $data = DB::table('penyakit')
                ->join('diagnosa_pasien', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
                ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'diagnosa_pasien.no_rawat')
                ->select(
                    'penyakit.kd_penyakit',
                    DB::raw('SUBSTRING(penyakit.nm_penyakit, 1, 80) as nm_penyakit'),
                    DB::raw('count(distinct reg_periksa.no_rawat) as jumlah_pasien')
                )
                ->where('reg_periksa.status_lanjut', 'Ralan')
                ->where('diagnosa_pasien.status', 'Ralan')
                ->where('diagnosa_pasien.prioritas', '1')
                ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
                ->groupBy('penyakit.kd_penyakit', 'penyakit.nm_penyakit')
                ->orderByDesc('jumlah_pasien')
                ->limit(10)
                ->get();

            return $this->successResponse([
                'tanggal_awal' => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
                'top_10_penyakit' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada server');
        }
    }

    public function top10Ranap(Request $request){
        [$tanggalAwal, $tanggalAkhir] = $this->periodeDefault();
        $tanggalAwal = $request->query('tanggal_awal', $tanggalAwal);
        $tanggalAkhir = $request->query('tanggal_akhir', $tanggalAkhir);

        try {
        $data = DB::table('penyakit')
            ->join('diagnosa_pasien', 'penyakit.kd_penyakit', '=', 'diagnosa_pasien.kd_penyakit')
            ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'diagnosa_pasien.no_rawat')
            ->select(
                'penyakit.kd_penyakit',
                DB::raw('SUBSTRING(penyakit.nm_penyakit, 1, 80) as nm_penyakit'),
                DB::raw('count(distinct reg_periksa.no_rawat) as jumlah_pasien')
            )
            ->where('reg_periksa.status_lanjut', 'Ranap')
            ->where('diagnosa_pasien.status', 'Ranap')
            ->where('diagnosa_pasien.prioritas', '1')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->groupBy('penyakit.kd_penyakit', 'penyakit.nm_penyakit')
            ->orderByDesc('jumlah_pasien')
            ->limit(10)
            ->get();

        return $this->successResponse([
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'top_10_penyakit' => $data,
        ]);
    } catch (\Exception $e) {
        return $this->errorResponse('Terjadi kesalahan pada server');
    }

    }

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
