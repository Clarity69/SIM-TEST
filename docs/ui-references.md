# Referensi UI

Mobbin MCP tidak dapat dipakai (akun membutuhkan paket berbayar), sehingga referensi diambil dari
pola desain web yang clean dan modern berikut. Mockup Framer: https://framer.com/projects/m4YVCa9Q5gOj6KF0G9Yf

| Referensi | Pola yang diambil | Dipakai di | Alasan |
|---|---|---|---|
| [shadcn/ui Tasks data table](https://www.shadcn.io/data-table-examples) | Toolbar di atas tabel: kotak cari + filter berbentuk chip (faceted) + tombol reset; checkbox per baris; pagination dengan info "x–y dari z" | Data Mahasiswa | Filter terlihat sebagai chip sehingga user tahu filter apa yang aktif tanpa membuka dropdown |
| [ReUI table](https://reui.io/components/table) | Bulk action bar yang muncul hanya ketika ada baris terpilih (jumlah terpilih + aksi: ubah status, export, hapus) | Data Mahasiswa | Aksi massal tidak memenuhi toolbar saat tidak dipakai; aksi destruktif dipisah warnanya |
| [How to build a modern admin dashboard with shadcn/ui](https://dev.to/ausrobdev/how-to-build-a-modern-admin-dashboard-with-shadcnui-in-2026-3477) | App shell: sidebar dengan grup menu, topbar dengan breadcrumb + profil; halaman pengaturan berupa kartu per section | Layout, Profil | Hierarki jelas: navigasi tetap di kiri, konteks halaman di topbar |
| [SaaSFrame — Import & Export](https://www.saasframe.io/categories/import-export) | Unduh template dipisah dari aksi import; ringkasan hasil (berhasil/gagal) setelah proses | Import | User mendapat struktur kolom yang benar sebelum memilih file |
| [CSVBox — multi-step import wizard](https://blog.csvbox.io/build-multi-step-csv-import-wizard-react-saas/) | Stepper 3 langkah: Unggah → Pratinjau & validasi → Hasil; error per baris dengan nomor baris dan kolom | Import | Kesalahan data ditunjukkan per baris sehingga file mudah diperbaiki |
| [5 SaaS UI patterns (DEV)](https://dev.to/0xsaifdev/5-saas-ui-patterns-every-developer-should-steal-with-implementation-examples-kpe) | Command palette Ctrl+K: pencarian dengan debounce, navigasi panah + Enter | Prioritas B | Akses cepat ke mahasiswa/prodi/halaman tanpa mouse |
| Pola tempat sampah (Gmail/Notion/Google Drive) | Daftar item terhapus dengan waktu hapus dan siapa yang menghapus; aksi Pulihkan (utama) dan Hapus permanen (destruktif, dengan konfirmasi); banner keterangan | Sampah | Restore menjadi aksi paling mudah, hapus permanen dipersulit karena tidak bisa dibatalkan |

## Prinsip yang dipakai di semua halaman

- **Satu warna dominan**: biru STITEK `primary-600` untuk aksi utama; oranye hanya aksen (penanda menu aktif, garis kop email).
- **Kontras AA**: teks putih hanya di atas `primary-600` ke atas; teks sekunder `#5B6475` di atas putih (6.1:1).
- **Kepadatan sedang**: kartu putih radius 14px, border tipis, bayangan halus; jarak antarseksi 24px.
- **Status lewat badge**, bukan teks berwarna polos: Aktif (hijau), Cuti (kuning), Lulus (biru), Keluar (abu).
- **Aksi destruktif selalu dua langkah**: modal konfirmasi, tombol merah, teks yang menyebut akibatnya.
