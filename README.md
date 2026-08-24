# Surface Mine Production — Sistem Rekapan Operator dan Pemantauan Lapangan

<p align="center">
  <img src="public/icons/icon-192x192.png" width="120" height="120" alt="Surface Mine Production">
</p>

<p align="center">
  <a href="https://github.com/dimss19/surface-mine-monitoring"><img src="https://img.shields.io/badge/status-active-brightgreen" alt="Status"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-red" alt="Laravel 13.x"></a>
  <a href="https://www.php.net/releases/8.3/"><img src="https://img.shields.io/badge/PHP-8.3+-purple" alt="PHP 8.3+"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue" alt="License"></a>
</p>

---

## Tentang Proyek

**Surface Mine Production** adalah sistem informasi berbasis web untuk mendigitalkan rekapan operator Hour Meter (HM) alat berat di area tambang permukaan serta pemantauan lapangan oleh Supervisor. Sistem memiliki **3 role pengguna** dan mendukung **operasi offline penuh** untuk area terpencil.

| Role | Deskripsi |
|------|-----------|
| **Pegawai** | Login → isi rekapan operator HM alat berat (tanggal, shift, area, alat, HM awal/akhir, tipe pekerjaan) |
| **SPV** | Login → dashboard, membuat laporan pemantauan lapangan (deskripsi, kendala, progress, upload foto) |
| **Admin** | Login → dashboard, kelola master data (SPV, Alat, Pegawai, Area), export laporan ke Excel |

### Fitur Utama

- Landing page publik dengan tombol Masuk
- Login berbasis session untuk semua role (admin / spv / pegawai)
- Role-based middleware (`role:admin`, `role:spv`, `role:pegawai`)
- Dashboard SPV: filter laporan, buat laporan baru, detail laporan + foto
- Dashboard Admin: CRUD master data (SPV, Alat, Pegawai), filter + export laporan Excel
- **Offline-first**: IndexedDB untuk penyimpanan data sementara — auto-sync saat online
- **PWA** (Progressive Web App) — install ke homescreen Android, service worker cache shell
- **Replay safety**: deteksi duplikat (tanggal+shift+pegawai_id / spv_id+area_id+tanggal+shift), return 200 saat replay
- **CSRF token refresh** untuk setiap replay offline
- Upload foto lapangan via Spatie MediaLibrary (max 5MB per file)
- Backward compatibility: `/absensi` dan `/rekapan` redirect ke `/pegawai/rekapan`

---

## Tech Stack

| Teknologi | Keterangan | Alasan |
|-----------|------------|--------|
| **PHP 8.3+** | Bahasa pemrograman backend | Maturitas tinggi, ekosistem Laravel matang, performa baik untuk web app monolitik, dukungan JIT dan type system modern |
| **Laravel 13.x** | Full-stack PHP framework | Routing ekspresif, Eloquent ORM memudahkan query database, middleware role-based, queue/job untuk proses async, Artisan CLI untuk produktivitas, ekosistem package yang luas |
| **Filament** | Admin panel builder | CRUD generator instant untuk dashboard Admin (kelola SPV, alat, pegawai), komponen UI siap pakai (tabel, form, filter), mendukung export + role-based access, mempercepat development dashboard admin secara drastis |
| **Maatwebsite/Laravel-Excel** | Export/Import Excel | Fitur export laporan ke Excel di dashboard Admin, mendukung format XLSX dengan styling dan chunk reading untuk data besar |
| **MySQL** | Database relasional | Cocok untuk data terstruktur dengan relasi (pegawai ↔ absensi, SPV ↔ area, dll), transaksional ACID, performa baik untuk query join, didukung penuh oleh Laragon |
| **Eloquent ORM** | Database abstraction layer | Mapping 1:1 dengan tabel database, eager loading optimasi query, mendukung relasi (belongsTo, hasMany, morphMany), migration version control untuk skema database |
| **Blade** | Templating engine Laravel | Component-based (data-table, form-input, photo-uploader, spv-card, select-dropdown, progress-input), layout inheritance, sintaks bersih, kompatibel penuh dengan data offline dan CSRF |
| **Alpine.js** | JavaScript reaktif ringan | Interaktivitas UI tanpa build step berat (show/hide, form binding, event handling), ukuran ~7kB, ideal untuk enhancement pada Blade tanpa perlu Vue/React penuh |
| **Tailwind CSS 4.x** | Utility-first CSS | Development cepat tanpa menulis CSS kustom, bundle kecil berkat JIT + purge, desain konsisten dengan utility classes, dark mode built-in untuk tampilan industrial |
| **Vite** | Build tool & HMR dev server | HMR instan untuk development frontend, integrasi first-class dengan Laravel via `laravel-vite-plugin`, tree-shaking dan code splitting untuk produksi |
| **Session-based Auth** | Autentikasi server-side | 3 role login (admin/spv/pegawai) dengan guard web default, middleware `role:` untuk proteksi route, session terenkripsi, cocok untuk web tradisional tanpa SPA |
| **Argon2id** | Password hashing | Algoritma hashing terkuat saat ini (pemenang PHC), tahan terhadap GPU/ASIC attacks, direkomendasikan OWASP, built-in di PHP 8.3+ |
| **Spatie Laravel MediaLibrary** | Upload & manage media | Upload foto lapangan dari SPV, auto-konversi thumbnail, organisasi file per model (PemantauanLapangan), integrasi mudah dengan Eloquent dan Blade |
| **IndexedDB** | Client-side storage (offline) | Database NoSQL di browser untuk menyimpan data absensi saat offline, kapasitas besar ( > 250MB ), mendukung structured data dan transaksional query |
| **Service Worker** | PWA offline caching | Cache shell aplikasi (CSS, JS, HTML) agar tetap bisa diakses di area tambang tanpa sinyal, intercept fetch request, fallback ke `offline.html` |
| **Queue (database driver)** | Job processing | Menjalankan proses berat di background (export Excel, upload foto, sync data), antrian disimpan di tabel MySQL, dijalankan via `php artisan queue:listen` |
| **Material Symbols** | Icon font (Google Fonts) | Vektor ikon konsisten dengan bobot variabel (FILL, wght, GRAD), ringan (subset), mudah dikustom ukuran dan warna via CSS font-variation-settings |

---

## Persyaratan Sistem

1. PHP >= 8.3 (ekstensi: fileinfo, pdo_mysql, mbstring, gd, xml, curl, bcmath, json, openssl, tokenizer)
2. Composer
3. MySQL / MariaDB (via Laragon atau XAMPP)
4. Node.js dan NPM
5. Git

---

## Instalasi

### Langkah 1 — Clone Repository

```bash
git clone https://github.com/dimss19/surface-mine-monitoring.git
cd surface-mine-production
```

### Langkah 2 — Install Dependency PHP

```bash
composer install
```

### Langkah 3 — Install Dependency Frontend & Build

```bash
npm install
npm run build
```

### Langkah 4 — Konfigurasi Environment

```bash
cp .env.example .env
```

Generate APP_KEY:

```bash
php artisan key:generate
```

### Langkah 5 — Buat Database MySQL

```sql
CREATE DATABASE IF NOT EXISTS surface_mine_production
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### Langkah 6 — Sesuaikan .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=surface_mine_production
DB_USERNAME=root
DB_PASSWORD=
```

### Langkah 7 — Migration & Seeder

```bash
php artisan migrate --seed
```

**Akun Default (Seeder):**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@surface-mine.com | password |
| SPV | spv1@surface-mine.com | password |
| Pegawai | pegawai.1@mine.local | password |

### Langkah 8 — Buat Symlink Storage

```bash
php artisan storage:link
```

### Langkah 9 — Jalankan Aplikasi

```bash
php artisan serve
```

Akses di browser: http://localhost:8000

---

## Tautan Penting

| Halaman | URL | Akses |
|---------|-----|-------|
| Landing Page | `/` | Publik |
| Login | `/login` | Publik |
| Dashboard Pegawai | `/pegawai` | Pegawai |
| Form Rekapan Operator | `/pegawai/rekapan` | Pegawai |
| Riwayat Rekapan | `/pegawai/riwayat` | Pegawai |
| Dashboard SPV | `/spv/dashboard` | SPV |
| Laporan Pemantauan | `/spv/pemantauan` | SPV |
| Dashboard Admin | `/admin/dashboard` | Admin |
| Kelola SPV | `/admin/spv` | Admin |
| Kelola Alat | `/admin/alat` | Admin |
| Kelola Pegawai | `/admin/pegawai` | Admin |
| Export Excel | `/admin/export` | Admin |

---

## Struktur Database

| Migration | Tabel |
|-----------|-------|
| `0001_01_01_000000` | users, password_reset_tokens, sessions |
| `0001_01_01_000001` | cache, cache_locks |
| `0001_01_01_000002` | jobs, job_batches, failed_jobs |
| `2026_07_17_154506` | areas |
| `2026_07_17_154507` | pegawais |
| `2026_07_17_154508` | alats |
| `2026_07_17_154509` | absensi_pegawais (rekapan operator) |
| `2026_07_17_154510` | pemantauan_lapangans |
| `2026_07_17_154511` | pemantauan_fotos |
| `2026_07_17_154512` | area_spv (pivot) |
| `2026_07_17_155928` | media (Spatie) |
| `2026_07_21_000001` | add role pegawai ke users |
| `2026_07_21_000002` | add pegawai_id ke users |
| `2026_07_22_000001` | change kode → jenis di alats |

---

## Offline Architecture

```
User Submit Form (data-offline-form)
        │
        ├── Online ──→ POST AJAX ──→ Server
        │                              │
        │                    ┌── Sukses ──→ Toast "Data berhasil dikirim"
        │                    └── Gagal ──→ Fallback ke IndexedDB
        │
        └── Offline ──→ IndexedDB (outbox)
                        │
                        └── Online event ──→ replayOutbox()
                                              │
                         GET /csrf-token (fresh)
                         POST replay (X-Offline-Replay: 1)
                              │
                          Duplicate? ──→ 200 OK (no insert)
                              │
                          New data ──→ Insert + 200 OK
```

### Komponen Offline

| File | Peran |
|------|-------|
| `resources/js/offline-sync.js` | IndexedDB CRUD, form interceptor, replay engine, toast notification |
| `public/sw.js` | Service Worker: cache app shell, assets, fallback offline.html |
| `public/manifest.json` | PWA manifest (display standalone, icons) |
| `public/offline.html` | Fallback page saat offline |
| `routes/web.php:20` | `GET /csrf-token` endpoint untuk refresh token |

---

## Struktur Direktori (Inti)

```
surface-mine-production/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php           # Login / Logout
│   │   ├── PegawaiController.php         # Rekapan operator pegawai
│   │   ├── SpvController.php             # SPV dashboard
│   │   ├── SpvPemantauanController.php   # CRUD pemantauan + foto
│   │   ├── AdminController.php           # Dashboard + export
│   │   ├── AdminSpvController.php        # CRUD SPV
│   │   ├── AdminAlatController.php       # CRUD alat
│   │   └── AdminPegawaiController.php    # CRUD pegawai
│   ├── Http/Middleware/RoleMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Pegawai.php
│       ├── Alat.php
│       ├── Area.php
│       ├── AbsensiPegawai.php
│       ├── PemantauanLapangan.php
│       └── PemantauanFoto.php
├── database/
│   ├── migrations/        # 14 migration files
│   └── seeders/           # AreaSeeder, AlatSeeder, PegawaiSeeder, UserSeeder
├── public/
│   ├── sw.js              # Service Worker
│   ├── manifest.json      # PWA Manifest
│   ├── offline.html       # Halaman offline fallback
│   └── icons/             # PWA icons (180x180, 192x192, 512x512)
├── resources/
│   ├── views/
│   │   ├── auth/login.blade.php
│   │   ├── layouts/
│   │   ├── pegawai/rekapan/  # create + index
│   │   ├── pegawai/
│   │   ├── spv/
│   │   ├── admin/
│   │   └── components/
│   └── js/
│       ├── app.js
│       └── offline-sync.js
└── routes/web.php
```

---

## Catatan Risiko

| Risiko | Penanganan |
|--------|------------|
| iOS Safari tidak support Background Sync | Fallback: `online` event + sync-on-load saat tab terbuka |
| CSRF token basi saat replay offline | Endpoint `GET /csrf-token` untuk ambil token fresh sebelum tiap POST replay |
| ALTER TABLE enum role di SQLite | Migration `2026_07_21_000001` skip SQLite (`if (DB::getDriverName() === 'sqlite') return`) |
| Ukuran foto > 5MB | Validasi `max:5120` di SpvPemantauanController |
| Duplikat data saat replay | Deteksi kombinasi unik (tanggal+shift+pegawai_id / spv_id+area_id+tanggal+shift), return 200 jika duplikat |

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `composer install` error | Pastikan PHP >= 8.3 dan ekstensi aktif: fileinfo, pdo_mysql, mbstring, gd, xml, curl, bcmath, json, openssl, tokenizer |
| PDOException: could not find driver | Aktifkan ekstensi pdo_mysql di php.ini, restart web server |
| SQLSTATE[HY000] [1049] Unknown database | Buat database `surface_mine_production` |
| 403 Forbidden saat akses /storage/ | Jalankan `php artisan storage:link` |
| White screen / Error 500 | Cek `storage/logs/laravel.log` |
| Vite error / HMR tidak jalan | Jalankan `npm install && npm run build` |
| Class not found (setelah update) | Jalankan `composer dump-autoload` |
| PWA tidak bisa install | Pastikan akses via HTTPS atau localhost, icons sudah ada di `public/icons/` |

---

## Reset Database (Development)

```bash
php artisan migrate:fresh --seed
```

---

## Lisensi

Proyek ini adalah open-source dengan lisensi [MIT](https://opensource.org/licenses/MIT).

---

## Kontributor

- **Dimss19** — Developer dan Maintainer
