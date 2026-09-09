# II. Daftar Berkas yang Diubah / Dibuat

Berikut adalah daftar lengkap berkas yang diubah dan dibuat pada proses audit, perbaikan anomali operasional, dan penyelarasan wewenang sistem:

| No | Tipe | Path Berkas | Ringkasan Perubahan |
|---|---|---|---|
| 1 | **NEW** | `database/migrations/2026_09_09_000001_drop_orphan_tables.php` | Migrasi pembersihan 6 tabel usang peninggalan skema lama: `absensi_pegawais`, `alats`, `pemantauan_lapangans`, `pemantauan_fotos`, `material_movements`, dan `unit_fuel_logs`. |
| 2 | **NEW** | `resources/views/operator/general/index.blade.php` | Halaman riwayat pekerjaan general operator (`/pegawai/general/riwayat`) lengkap dengan filter tanggal dan pagination mandiri. |
| 3 | **MODIFIED** | `app/Http/Controllers/UtilizationController.php` | Menambahkan pengecekan role operator-only (`pegawai`) pada method `create()` dan `store()`, menghapus pembatasan `user_id` lama agar operator pengganti dapat mencabut status unit breakdown/servis menjadi `ready`. |
| 4 | **MODIFIED** | `app/Http/Requests/StoreUtilizationRequest.php` | Memperbarui method `authorize()` agar membatasi akses pengiriman status utilization hanya untuk pengguna dengan role `pegawai`. |
| 5 | **MODIFIED** | `app/Services/DashboardReportService.php` | Memperbaiki formula matematika PA dan UA (penerapan *range-clamping* irisan jam breakdown PostgreSQL `GREATEST`/`LEAST`, kalkulasi Scheduled Hours 24h/12h per shift), memasukkan unit non-ritasi ke working hours & fuel, serta deteksi ore dinamis. |
| 6 | **MODIFIED** | `app/Http/Controllers/RekapanController.php` | Memisahkan query tugas General (`whereNull unit_id`) dengan Non-Ritasi (`whereNotNull unit_id`), serta menerapkan pagination 5 item per halaman secara independen (`ritasi_page`, `non_ritasi_page`, `general_page`). |
| 7 | **MODIFIED** | `app/Http/Controllers/PegawaiRitasiController.php` | Menambahkan validasi konkurensi (mencegah duplikasi unit yang sama di tanggal & shift yang sama) dan validasi batas maksimal HM operasi ($\le 12\text{ jam}$). |
| 8 | **MODIFIED** | `app/Http/Controllers/PegawaiNonRitasiController.php` | Menambahkan validasi konkurensi unit, mengizinkan jam kerja shift malam lintas hari, dan membatasi pengecekan duplikasi pada `whereNotNull('unit_id')`. |
| 9 | **MODIFIED** | `app/Http/Controllers/PegawaiGeneralController.php` | Menyesuaikan pengecekan duplikasi pada tugas general (`whereNull unit_id`) dan menambahkan method `riwayat()` untuk menampilkan riwayat general. |
| 10 | **MODIFIED** | `app/Http/Controllers/AdminUnitController.php` | Menghapus pemanggilan relasi eager loading `with('area')` pada query User yang tidak ada pada skema database. |
| 11 | **MODIFIED** | `app/Models/User.php` | Menghapus definisi relasi usang `belongsTo(Unit)` dan `belongsTo(Area)`. |
| 12 | **MODIFIED** | `app/Models/Pegawai.php` | Menambahkan accessor `nik` untuk mengambil username akun operator (`$this->user?->username`). |
| 13 | **MODIFIED** | `routes/web.php` | Memindahkan rute refresh `/csrf-token` ke luar middleware `auth` untuk offline sync, dan mendaftarkan rute `pegawai/general/riwayat`. |
| 14 | **MODIFIED** | `resources/views/operator/utilization/create.blade.php` | Menampilkan label status unit pada dropdown, auto-select status `Ready (Siap Kerja / Cabut)` ketika unit breakdown dipilih, dan pengisian otomatis waktu lokal. |
| 15 | **MODIFIED** | `resources/views/dashboard/partials/daily.blade.php` | Menampilkan nilai persentase PA dan UA secara berdampingan dan rapi pada dashboard harian. |
| 16 | **MODIFIED** | `resources/views/operator/general/create.blade.php` | Menambahkan tombol tautan navigasi menuju halaman riwayat pekerjaan general. |
| 17 | **MODIFIED** | `resources/views/operator/non-ritasi/create.blade.php` | Menyesuaikan navigasi riwayat dan panduan form non-ritasi. |
| 18 | **MODIFIED** | `resources/views/operator/ritasi/create.blade.php` | Menyesuaikan navigasi riwayat dan panduan input QTY total muatan. |
| 19 | **MODIFIED** | `resources/views/operator/partials/data-dasar.blade.php` | Menyesuaikan label nomor unit dinamis sesuai kategori unit alat berat. |
| 20 | **MODIFIED** | `resources/views/rekapan/index.blade.php` | Menampilkan kolom NIK operator dan penyesuaian filter rekapan. |
| 21 | **MODIFIED** | `resources/views/rekapan/show.blade.php` | Implementasi pagination 5 item per tabel untuk Ritasi, Non-Ritasi, dan Tugas General. |
| 22 | **MODIFIED** | `resources/views/rekapan/export/excel.blade.php` | Penyelarasan kolom export excel detail rekapan dengan format data operasional terbaru. |
| 23 | **MODIFIED** | `database/seeders/NonRitasiSeeder.php` | Menyertakan data tanggal hari ini (day 0) dan mencegah tabrakan penugasan operator dengan data ritasi. |
| 24 | **MODIFIED** | `database/seeders/RitasiSeeder.php` | Menyertakan data tanggal hari ini (day 0) dan menyaring area penugasan non-hauling (Workshop & Fuel Station). |
| 25 | **MODIFIED** | `public/sw.js` | Menaikkan versi cache ke `surface-mine-v5` dan mendaftarkan rute create/riwayat operator ke App Shell. |
| 26 | **MODIFIED** | `resources/js/offline-sync.js` | Penambahan error handling dan verifikasi respons JSON pada pengambilan CSRF token offline replay. |
