<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenyakitController extends Controller
{

    private array $kategoriPenyakit = [
        'kanker' => [
            'label' => 'Kanker (Neoplasma Ganas)',
            'range' => ['C00', 'C97'],
            'kode_acuan' => [
                'C34.9' => 'Kanker Paru',
                'C50.9' => 'Kanker Payudara',
                'C53.9' => 'Kanker Serviks',
                'C18.9' => 'Kanker Kolon/Usus Besar',
            ],
        ],
        'jantung' => [
            'label' => 'Jantung & Pembuluh Darah',
            'range' => ['I20', 'I52'],
            'kode_acuan' => [
                'I21.9' => 'Infark Miokard Akut',
                'I20.9' => 'Angina Pectoris',
                'I50.9' => 'Gagal Jantung',
                'I25.1' => 'Penyakit Jantung Koroner',
            ],
        ],
        'stroke' => [
            'label' => 'Stroke (Serebrovaskular)',
            'range' => ['I60', 'I69'],
            'kode_acuan' => [
                'I63.9' => 'Stroke Iskemik',
                'I61.9' => 'Stroke Hemoragik',
                'I64' => 'Stroke Tidak Spesifik',
            ],
        ],
        'uronefro' => [
        'label' => 'Ginjal & Saluran Kemih',
        'ranges' => [
            ['N00', 'N39'],
            ['Q60', 'Q64'],
        ],
        'kode_acuan' => [
            'N18.5' => 'Penyakit Ginjal Kronik Stadium 5',
            'N18.9' => 'Penyakit Ginjal Kronik Tidak Spesifik',
            'N20.0' => 'Batu Ginjal',
            'N17.9' => 'Gagal Ginjal Akut',
        ],
    ],
    ];
    
    public function top10Ralan(Request $request)
    {
        [$tanggalAwal, $tanggalAkhir] = $this->resolveTanggal($request);
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
        [$tanggalAwal, $tanggalAkhir] = $this->resolveTanggal($request);
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

    /**
 * GET /api/penyakit/kanker
 * Jumlah kasus kanker (Ralan + Ranap digabung)
 */
public function kanker(Request $request)
{
    return $this->responkategoriPenyakit($request, 'kanker');
}

/**
 * GET /api/penyakit/jantung
 * Jumlah kasus jantung & pembuluh darah (Ralan + Ranap digabung)
 */
public function jantung(Request $request)
{
    return $this->responkategoriPenyakit($request, 'jantung');
}

/**
 * GET /api/penyakit/stroke
 * Jumlah kasus stroke (Ralan + Ranap digabung)
 */
public function stroke(Request $request)
{
    return $this->responkategoriPenyakit($request, 'stroke');
}

/**
 * GET /api/penyakit/uronefro
 * Jumlah kasus ginjal & saluran kemih (Ralan + Ranap digabung)
 */
public function uronefro(Request $request)
{
    return $this->responkategoriPenyakit($request, 'uronefro');
}

/**
 * Helper privat: logika inti yang dipakai ke-4 method di atas.
 * Supaya tidak nulis kode yang sama 4 kali.
 */
private function responkategoriPenyakit(Request $request, string $key)
{
    [$tanggalAwal, $tanggalAkhir] = $this->resolveTanggal($request);
    try {
        $kategori = $this->kategoriPenyakit[$key];

            $totalKategori = $this->hitungJumlah(
            $tanggalAwal,
            $tanggalAkhir,
            ranges: $kategori['ranges']
        );

        $perJenis = [];
        foreach ($kategori['kode_acuan'] as $kode => $nama) {
            $perJenis[] = [
                'kd_penyakit' => $kode,
                'nama' => $nama,
                'jumlah_kasus' => $this->hitungJumlah($tanggalAwal, $tanggalAkhir, kodeSpesifik: $kode),
            ];
        }

        $jumlahAcuan = array_sum(array_column($perJenis, 'jumlah_kasus'));
        $perJenis[] = [
            'kd_penyakit' => null,
            'nama' => $kategori['label'] . ' Lainnya',
            'jumlah_kasus' => max(0, $totalKategori - $jumlahAcuan),
        ];

        return $this->successResponse([
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'keterangan' => 'Jumlah gabungan rawat jalan (Ralan) dan rawat inap (Ranap)',
            'label' => $kategori['label'],
            'total_kasus' => $totalKategori,
            'per_jenis' => $perJenis,
        ]);
    } catch (\Exception $e) {
        return $this->errorResponse('Terjadi kesalahan pada server');
    }
}   

    private function hitungJumlah(
    string $tanggalAwal,
    string $tanggalAkhir,
    ?string $kodeSpesifik = null,
    ?array $ranges = null
    ): int {
    $query = DB::table('diagnosa_pasien')
        ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'diagnosa_pasien.no_rawat')
        ->whereColumn('diagnosa_pasien.status', 'reg_periksa.status_lanjut')
        ->whereIn('reg_periksa.status_lanjut', ['Ralan', 'Ranap'])
        ->where('diagnosa_pasien.prioritas', '1')
        ->whereBetween('reg_periksa.tgl_registrasi', [$tanggalAwal, $tanggalAkhir]);

    if ($kodeSpesifik) {
        $query->where('diagnosa_pasien.kd_penyakit', $kodeSpesifik);
    } elseif ($ranges) {
        // Bungkus semua range dalam satu grup kurung, dihubungkan pakai OR
        $query->where(function ($q) use ($ranges) {
            foreach ($ranges as $range) {
                $q->orWhereBetween('diagnosa_pasien.kd_penyakit', [$range[0], $range[1]]);
            }
        });
    }

    return $query->count(DB::raw('distinct reg_periksa.no_rawat'));
}



}
