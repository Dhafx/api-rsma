# 📋 Dokumentasi API SIMRS

API ini menyediakan data kunjungan dan penyakit dari Sistem Informasi Manajemen Rumah Sakit (SIMRS).
Dibangun dengan **Laravel 12**, seluruh endpoint dilindungi dengan **API Key** dan mengembalikan data dalam format **JSON**.

---

## 🔐 Autentikasi

Setiap request **wajib** menyertakan header berikut:

```http
X-API-KEY: {api_key_anda}
```

Request tanpa header ini akan mendapat respons `401 Unauthorized`.

---

## 📅 Sistem Periode Tanggal

Semua endpoint mendukung **3 cara penggunaan** yang konsisten:

### 1. Periode Default Otomatis
Tidak perlu mengirim parameter apapun. Sistem akan otomatis menentukan periode dari **tanggal 5 bulan ini** hingga **tanggal 4 bulan depan**.

```http
GET /api/v1/kunjungan-ralan
```

> **Logika periode default:**
> - Jika hari ini ≥ tanggal 5 → periode dimulai tanggal 5 bulan berjalan
> - Jika hari ini < tanggal 5 → periode dimulai tanggal 5 bulan sebelumnya
> - Periode berakhir tepat 1 bulan setelah tanggal awal (dikurangi 1 hari)

---

### 2. Periode Custom
Kirim parameter `tanggal_awal` dan/atau `tanggal_akhir` untuk menentukan periode sendiri.

```http
GET /api/v1/kunjungan-ralan?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

| Parameter | Tipe | Wajib | Deskripsi |
|---|---|---|---|
| `tanggal_awal` | `string` | Tidak | Tanggal mulai. Format: `YYYY-MM-DD` |
| `tanggal_akhir` | `string` | Tidak | Tanggal selesai. Format: `YYYY-MM-DD`. Harus ≥ `tanggal_awal` |

---

### 3. Validasi Format Tanggal (Error 422)
Jika format tanggal yang dikirim **tidak sesuai** (`YYYY-MM-DD`), API akan langsung mengembalikan error `422 Unprocessable Entity` dengan pesan yang jelas.

**Contoh request salah:**
```http
GET /api/v1/kunjungan-ralan?tanggal_awal=05-01-2026
```

**Respons error 422:**
```json
{
  "message": "Format tanggal_awal harus YYYY-MM-DD, contoh: 2026-08-05",
  "errors": {
    "tanggal_awal": [
      "Format tanggal_awal harus YYYY-MM-DD, contoh: 2026-08-05"
    ]
  }
}
```

**Pesan validasi yang mungkin muncul:**

| Kondisi | Pesan Error |
|---|---|
| Format `tanggal_awal` salah | `Format tanggal_awal harus YYYY-MM-DD, contoh: 2026-08-05` |
| Format `tanggal_akhir` salah | `Format tanggal_akhir harus YYYY-MM-DD, contoh: 2026-09-04` |
| `tanggal_akhir` sebelum `tanggal_awal` | `tanggal_akhir tidak boleh sebelum tanggal_awal` |

---

## 📦 Format Respons

### ✅ Sukses
```json
{
  "success": true,
  "instansi": {
    "nama_instansi": "RSUD Contoh",
    "kode_ppk_kemenkes": "1234567"
  },
  "data": {
    "tanggal_awal": "2026-08-05",
    "tanggal_akhir": "2026-09-04",
    "..."
  }
}
```

### ❌ Error Server (500)
```json
{
  "success": false,
  "instansi": {
    "nama_instansi": "RSUD Contoh",
    "kode_ppk_kemenkes": "1234567"
  },
  "message": "Terjadi kesalahan pada server"
}
```

---

## 🏥 Endpoint: Kunjungan Rawat Jalan (Ralan)

### `GET /api/v1/kunjungan-ralan`
Jumlah total kunjungan rawat jalan dalam satu periode.

**Contoh Request:**
```http
GET /api/v1/kunjungan-ralan
GET /api/v1/kunjungan-ralan?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "jumlah_kunjungan_ralan": 3240
  }
}
```

---

### `GET /api/v1/kunjungan-ralan/per-pj`
Jumlah kunjungan rawat jalan dikelompokkan per **jenis pembayaran** (BPJS, Umum, dll), diurutkan dari terbanyak.

**Contoh Request:**
```http
GET /api/v1/kunjungan-ralan/per-pj
GET /api/v1/kunjungan-ralan/per-pj?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "per_jenis_pembayaran": [
      { "kd_pj": "BPJ", "jenis_pembayaran": "BPJS", "jumlah_kunjungan": 2500 },
      { "kd_pj": "UMM", "jenis_pembayaran": "Umum", "jumlah_kunjungan": 740 }
    ]
  }
}
```

---

### `GET /api/v1/kunjungan-ralan/per-tanggal`
Jumlah kunjungan rawat jalan dikelompokkan per tanggal dalam periode.

**Contoh Request:**
```http
GET /api/v1/kunjungan-ralan/per-tanggal
GET /api/v1/kunjungan-ralan/per-tanggal?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

---

## 🛏️ Endpoint: Kunjungan Rawat Inap (Ranap)

### `GET /api/v1/kunjungan-ranap`
Jumlah total kunjungan rawat inap dalam satu periode.

> Data dihitung berdasarkan pasien **unik** (distinct `no_rawat`), mengecualikan status `Batal` dan rekam perpindahan kamar (`Pindah Kamar`).

**Contoh Request:**
```http
GET /api/v1/kunjungan-ranap
GET /api/v1/kunjungan-ranap?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "jumlah_kunjungan_ranap": 410
  }
}
```

---

### `GET /api/v1/kunjungan-ranap/per-pj`
Jumlah kunjungan rawat inap dikelompokkan per **jenis pembayaran**, diurutkan dari terbanyak.

> Data dihitung berdasarkan pasien **unik**, mengecualikan `Batal` dan `Pindah Kamar`.

**Contoh Request:**
```http
GET /api/v1/kunjungan-ranap/per-pj
GET /api/v1/kunjungan-ranap/per-pj?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "per_jenis_pembayaran": [
      { "kd_pj": "BPJ", "jenis_pembayaran": "BPJS", "jumlah_kunjungan": 310 },
      { "kd_pj": "UMM", "jenis_pembayaran": "Umum", "jumlah_kunjungan": 100 }
    ]
  }
}
```

---

## 🚨 Endpoint: Kunjungan IGD (Instalasi Gawat Darurat)

### `GET /api/v1/kunjungan-igd`
Jumlah total kunjungan IGD dalam satu periode.

> Data dihitung berdasarkan pasien **unik** (distinct `no_rawat`) yang terdaftar di poliklinik IGD (`kd_poli = 'IGDK'`).

**Contoh Request:**
```http
GET /api/v1/kunjungan-igd
GET /api/v1/kunjungan-igd?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "jumlah_kunjungan_igd": 187
  }
}
```

---

### `GET /api/v1/kunjungan-igd/per-pj`
Jumlah kunjungan IGD dikelompokkan per **jenis pembayaran**, diurutkan dari terbanyak.

**Contoh Request:**
```http
GET /api/v1/kunjungan-igd/per-pj
GET /api/v1/kunjungan-igd/per-pj?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "per_jenis_pembayaran": [
      { "kd_pj": "BPJ", "jenis_pembayaran": "BPJS", "jumlah_kunjungan": 130 },
      { "kd_pj": "UMM", "jenis_pembayaran": "Umum", "jumlah_kunjungan": 57 }
    ]
  }
}
```

---

## 🦠 Endpoint: Penyakit

### `GET /api/v1/penyakit-ralan/top10-ralan`
Top 10 penyakit terbanyak pada pasien **rawat jalan**, berdasarkan diagnosa **primer** (prioritas 1).

**Contoh Request:**
```http
GET /api/v1/penyakit-ralan/top10-ralan
GET /api/v1/penyakit-ralan/top10-ralan?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "top_10_penyakit": [
      { "kd_penyakit": "J06.9", "nm_penyakit": "Infeksi Saluran Pernapasan Atas Akut", "jumlah_pasien": 312 },
      { "kd_penyakit": "K29.7", "nm_penyakit": "Gastritis Tidak Spesifik", "jumlah_pasien": 198 }
    ]
  }
}
```

---

### `GET /api/v1/penyakit-ranap/top10-ranap`
Top 10 penyakit terbanyak pada pasien **rawat inap**, berdasarkan diagnosa **primer** (prioritas 1).

**Contoh Request:**
```http
GET /api/v1/penyakit-ranap/top10-ranap
GET /api/v1/penyakit-ranap/top10-ranap?tanggal_awal=2026-01-01&tanggal_akhir=2026-01-31
```

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "top_10_penyakit": [
      { "kd_penyakit": "I10", "nm_penyakit": "Hipertensi Esensial", "jumlah_pasien": 145 }
    ]
  }
}
```

---

### `GET /api/v1/penyakit/kanker`
Jumlah kasus **Kanker (Neoplasma Ganas)** — gabungan Ralan + Ranap.

Rentang kode ICD-10: `C00` – `C97`

**Contoh Respons:**
```json
{
  "success": true,
  "instansi": { "nama_instansi": "RSUD Contoh", "kode_ppk_kemenkes": "1234567" },
  "data": {
    "tanggal_awal": "2026-01-01",
    "tanggal_akhir": "2026-01-31",
    "keterangan": "Jumlah gabungan rawat jalan (Ralan) dan rawat inap (Ranap)",
    "label": "Kanker (Neoplasma Ganas)",
    "total_kasus": 87,
    "per_jenis": [
      { "kd_penyakit": "C34.9", "nama": "Kanker Paru", "jumlah_kasus": 22 },
      { "kd_penyakit": "C50.9", "nama": "Kanker Payudara", "jumlah_kasus": 18 },
      { "kd_penyakit": "C53.9", "nama": "Kanker Serviks", "jumlah_kasus": 15 },
      { "kd_penyakit": "C18.9", "nama": "Kanker Kolon/Usus Besar", "jumlah_kasus": 10 },
      { "kd_penyakit": null, "nama": "Kanker (Neoplasma Ganas) Lainnya", "jumlah_kasus": 22 }
    ]
  }
}
```

---

### `GET /api/v1/penyakit/jantung`
Jumlah kasus **Jantung & Pembuluh Darah** — gabungan Ralan + Ranap.

Rentang kode ICD-10: `I20` – `I52`

**Kode acuan:** `I21.9` (Infark Miokard Akut), `I20.9` (Angina Pectoris), `I50.9` (Gagal Jantung), `I25.1` (Penyakit Jantung Koroner)

---

### `GET /api/v1/penyakit/stroke`
Jumlah kasus **Stroke (Serebrovaskular)** — gabungan Ralan + Ranap.

Rentang kode ICD-10: `I60` – `I69`

**Kode acuan:** `I63.9` (Stroke Iskemik), `I61.9` (Stroke Hemoragik), `I64` (Stroke Tidak Spesifik)

---

### `GET /api/v1/penyakit/uronefro`
Jumlah kasus **Ginjal & Saluran Kemih** — gabungan Ralan + Ranap.

Rentang kode ICD-10: `N00`–`N39` dan `Q60`–`Q64`

**Kode acuan:** `N18.5` (CKD Stadium 5), `N18.9` (CKD Tidak Spesifik), `N20.0` (Batu Ginjal), `N17.9` (Gagal Ginjal Akut)

---

## 📋 Ringkasan Semua Endpoint

| Method | Endpoint | Deskripsi |
|---|---|---|
| `GET` | `/api/v1/kunjungan-ralan` | Total kunjungan rawat jalan |
| `GET` | `/api/v1/kunjungan-ralan/per-pj` | Kunjungan ralan per jenis pembayaran |
| `GET` | `/api/v1/kunjungan-ralan/per-tanggal` | Kunjungan ralan per tanggal |
| `GET` | `/api/v1/kunjungan-ranap` | Total kunjungan rawat inap |
| `GET` | `/api/v1/kunjungan-ranap/per-pj` | Kunjungan ranap per jenis pembayaran |
| `GET` | `/api/v1/kunjungan-igd` | Total kunjungan IGD |
| `GET` | `/api/v1/kunjungan-igd/per-pj` | Kunjungan IGD per jenis pembayaran |
| `GET` | `/api/v1/penyakit-ralan/top10-ralan` | Top 10 penyakit rawat jalan |
| `GET` | `/api/v1/penyakit-ranap/top10-ranap` | Top 10 penyakit rawat inap |
| `GET` | `/api/v1/penyakit/kanker` | Kasus kanker (Ralan + Ranap) |
| `GET` | `/api/v1/penyakit/jantung` | Kasus jantung & pembuluh darah (Ralan + Ranap) |
| `GET` | `/api/v1/penyakit/stroke` | Kasus stroke (Ralan + Ranap) |
| `GET` | `/api/v1/penyakit/uronefro` | Kasus ginjal & saluran kemih (Ralan + Ranap) |

> **Semua endpoint** menerima parameter opsional `tanggal_awal` dan `tanggal_akhir` (format `YYYY-MM-DD`).
> Jika tidak dikirim, sistem menggunakan **periode default otomatis** (tanggal 5 – tanggal 4 bulan depan).

---

## ⚠️ Kode Status HTTP

| Kode | Keterangan |
|---|---|
| `200` | Request berhasil |
| `401` | API Key tidak ada atau tidak valid |
| `422` | Format parameter tidak valid (misal: format tanggal salah) |
| `500` | Terjadi kesalahan pada server |
