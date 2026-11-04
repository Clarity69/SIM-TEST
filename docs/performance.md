# Catatan Performa Database

Diukur pada Supabase project `sim-tes` (PostgreSQL 17, region ap-northeast-2) dengan data seeder:
5 program studi × 20 mahasiswa = 100 baris. Semua angka diambil dengan
`EXPLAIN (ANALYZE, BUFFERS)` lewat Supabase MCP `execute_sql`, tanggal 5 November 2026.

## Index yang ditambahkan

| Index | Tipe | Untuk query |
|---|---|---|
| `mahasiswas_prodi_id_jenis_kelamin_index` | btree `(prodi_id, jenis_kelamin)` | filter prodi, filter prodi + JK (juga menggantikan index tunggal `prodi_id`) |
| `mahasiswas_jenis_kelamin_index` | btree | filter JK tanpa prodi |
| `mahasiswas_nama_index` | btree | sorting kolom nama |
| `mahasiswas_created_at_index` | btree | urutan default `latest()`, statistik 30 hari |
| `mahasiswas_nama_trgm_index` | GIN `gin_trgm_ops` | `nama ILIKE '%…%'` |
| `mahasiswas_nim_trgm_index` | GIN `gin_trgm_ops` | `nim ILIKE '%…%'` |

Extension `pg_trgm` dipasang di schema `extensions` (bukan `public`), sesuai rekomendasi Supabase.

## Pencarian + filter Data Mahasiswa

**Sebelum** (kode lama: `select *`, `LIKE`, filter prodi saja)

```
Seq Scan on mahasiswas  Filter: prodi_id = 1 AND (nim ~~ '%nur%' OR nama ~~ '%nur%')
Rows Removed by Filter: 100   → 0 baris ditemukan
Execution Time: 0.166 ms
```

`LIKE` di PostgreSQL membedakan huruf besar/kecil, sehingga pencarian "nur" **tidak menemukan**
"Nurul Laksita". Ini bug fungsional, bukan hanya soal performa.

**Sesudah** (`ILIKE`, kolom terpilih, filter prodi + JK, sort stabil)

```
Bitmap Heap Scan on mahasiswas
  Recheck Cond: prodi_id = 1 AND jenis_kelamin = 'Perempuan'
  -> Bitmap Index Scan on mahasiswas_prodi_id_jenis_kelamin_index  (rows=11)
Rows Removed by Filter: 10   → 1 baris ditemukan
Execution Time: 0.210 ms
```

Hanya 11 baris kandidat yang dibaca (dulu seluruh tabel).

**Pencarian teks tanpa filter** memakai index trigram:

```
Bitmap Heap Scan on mahasiswas
  -> BitmapOr
     -> Bitmap Index Scan on mahasiswas_nim_trgm_index
     -> Bitmap Index Scan on mahasiswas_nama_trgm_index
```

Catatan jujur: dengan 100 baris, planner masih memilih Seq Scan atau index `created_at` karena
tabel muat dalam 3 halaman disk. Plan di atas diambil dengan `SET LOCAL enable_seqscan = off`
untuk membuktikan index bisa dipakai. Planner akan memilihnya sendiri saat data membesar.

## Statistik Dashboard

**Sebelum**: 7 query terpisah di setiap request (count total, count L, count P, count prodi,
agregat 30 hari, 5 terbaru, per prodi), tanpa cache.

**Sesudah** (`App\Services\DashboardStats`):

| Kondisi | Query ke database |
|---|---|
| Cache kosong | 4: 1 agregat `COUNT(*) FILTER (...)`, 1 per prodi `withCount`, 1 terbaru + 1 eager load prodi |
| Cache terisi (5 menit) | 0 (dengan `CACHE_STORE=file`) |

Agregat 6 angka dalam satu query: `Seq Scan` 100 baris, `Execution Time: 0.297 ms`.
Cache dibersihkan otomatis lewat event model `Mahasiswa` dan `Prodi` (saved/deleted).

> Dengan `CACHE_STORE=database`, cache sendiri disimpan di Supabase sehingga tetap ada 1–2 query
> per request (diukur: 6 saat cache kosong, 1 saat terisi). Karena itu `.env.example` memakai
> `CACHE_STORE=file` dan `SESSION_DRIVER=file`.

## Halaman Data Mahasiswa

3 query per request (count pagination, data 10 baris, eager load prodi), tidak tergantung jumlah
baris di halaman. `Model::preventLazyLoading()` aktif di luar production sehingga N+1 langsung
menjadi exception saat development.

## Supabase Advisors

| | Sebelum | Sesudah |
|---|---|---|
| Security | 0 (belum ada tabel) | 11 × `rls_enabled_no_policy` (INFO), disengaja: RLS tanpa policy = tertutup untuk Data API; Laravel terhubung sebagai owner tabel |
| Performance | 0 | `unused_index` (INFO) untuk index yang baru dibuat dan index bawaan Laravel |

Tidak ada temuan level WARN/ERROR.
