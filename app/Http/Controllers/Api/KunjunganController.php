<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KunjunganController extends Controller
{
    public function jumlahRalanByDate(Request $request){
        [$tanggalAwal, $tanggalAkhir] = $this->periodeDefault();

        $tanggalAwal = $request->query('tanggal_awal', "2026-01-01");
        $tanggalAkhir = $request->query('tanggal_awal', "2026-05-01");

        try{
            $jumlah = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ranap')
            ->whereBetween('tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->count();

            return $this->successResponse([
                
                    'tanggal_awal' => $tanggalAwal,
                    'tanggal_akhir' => $tanggalAkhir,
                    'jumlah_kunjungan_ralan' => $jumlah,
              
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada server');
        }
    }

    public function jumlahRalan(Request $request){
        [$tanggalAwal, $tanggalAkhir] = $this->periodeDefault();
        $tanggalAwal = $request->query('tanggal_awal', $tanggalAwal);
        $tanggalAkhir = $request->query('tanggal_awal', $tanggalAkhir);

        try{
            $jumlah = DB::table('reg_periksa')
            ->where('status_lanjut', 'Ranap')
            ->whereBetween('tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->count();

            return $this->successResponse([
                
                    'tanggal_awal' => $tanggalAwal,
                    'tanggal_akhir' => $tanggalAkhir,
                    'jumlah_kunjungan_ralan' => $jumlah,
              
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada server');
        }
    }

    public function jumlahRalanPerPj(Request $request){
        [$tanggalAwal, $tanggalAkhir] = $this->periodeDefault();
        $tanggalAwal = $request->query('tanggal_awal', $tanggalAwal);
        $tanggalAkhir = $request->query('tanggal_awal', $tanggalAkhir);

        try {
        $data = DB::table('reg_periksa')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->select(
                'penjab.kd_pj',
                'penjab.png_jawab as jenis_pembayaran',
                DB::raw('count(*) as jumlah_kunjungan')
            )
            ->where('reg_periksa.status_lanjut', 'Ralan')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->groupBy('penjab.kd_pj', 'penjab.png_jawab')
            ->orderByDesc('jumlah_kunjungan')
            ->get();

        return $this->successResponse([
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'per_jenis_pembayaran' => $data,
        ]);
    } catch (\Exception $e) {
        return $this->errorResponse('Terjadi kesalahan pada server');
    }

        
    }
    public function jumlahRanap(Request $request){
        [$tanggalAwal, $tanggalAkhir] = $this->periodeDefault();
        $tanggalAwal = $request->query('tanggal_awal', $tanggalAwal);
        $tanggalAkhir = $request->query('tanggal_awal', $tanggalAkhir);

        try{
            $jumlah = DB::table('reg_periksa')
            ->join('kamar_inap', 'reg_periksa.no_rawat', '=', "kamar_inap.no_rawat")
            ->where('reg_periksa.status_lanjut', 'Ranap')
            ->where('reg_periksa.stts', '<>', 'Batal')
            ->where('kamar_inap.stts_pulang', '<>', 'Pindah Kamar')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->distinct('reg_periksa.no_rawat')
            ->count('reg_periksa.no_rawat');

            return $this->successResponse([
                
                    'tanggal_awal' => $tanggalAwal,
                    'tanggal_akhir' => $tanggalAkhir,
                    'jumlah_kunjungan_ranap' => $jumlah,
              
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan pada server');
        }
    }
    public function jumlahRanapPerPj(Request $request){
        [$tanggalAwal, $tanggalAkhir] = $this->periodeDefault();
        $tanggalAwal = $request->query('tanggal_awal', $tanggalAwal);
        $tanggalAkhir = $request->query('tanggal_awal', $tanggalAkhir);

        try {
        $data = DB::table('reg_periksa')
            ->join('kamar_inap', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->select(
                'penjab.kd_pj',
                'penjab.png_jawab as jenis_pembayaran',
                DB::raw('count(distinct reg_periksa.no_rawat) as jumlah_kunjungan')
            )
            ->where('reg_periksa.status_lanjut', 'Ranap')
            ->where('reg_periksa.stts', '<>', 'Batal')
            ->where('kamar_inap.stts_pulang', '<>', 'Pindah Kamar')
            ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir])
            ->groupBy('penjab.kd_pj', 'penjab.png_jawab')
            ->orderByDesc('jumlah_kunjungan')
            ->get();

        return $this->successResponse([
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'per_jenis_pembayaran' => $data,
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
