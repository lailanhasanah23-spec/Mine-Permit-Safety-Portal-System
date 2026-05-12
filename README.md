# Mine Permit & Safety Portal System

Portal ini digunakan untuk mengelola pengajuan Mine Permit dan SIMPER secara terpusat, dengan alur verifikasi berantai, kontrol akses berbasis peran, integrasi Google Workspace, dan audit log operasional.

## Ringkasan Fitur

- Pengajuan awal oleh Subcon / HRGA.
- Verifikasi medis oleh Paramedic.
- Verifikasi teknis oleh TOD.
- Persetujuan akhir oleh SHE / KTT.
- Manajemen kategori, form, user, audit log, dan workflow email oleh Admin.
- Integrasi Google Drive, Gmail, dan sinkronisasi Google Form.
- Quick Access Login untuk kebutuhan demo atau pengujian role.

## Tech Stack

- Backend: Laravel 13, PHP 8.3.
- Database: MySQL 8.0.
- Frontend: Tailwind CSS via Vite dengan komponen UI industrial kustom.
- Integrasi: Google Drive API, Gmail API, Google Form sync.
- Keamanan: Role-Based Access Control, transaksi database, pessimistic locking, lockout login.

## Persiapan Lingkungan

Untuk menjalankan proyek ini dengan stabil, siapkan:

- PHP 8.3
- Composer 2
- Node.js 22
- MySQL 8
- Git

Pastikan file `.env` diisi dari `.env.example`, lalu lengkapi minimal variabel berikut:

- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_MAILER=gmail`
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `ADMIN_EMAIL_WORKFLOW_ENABLED`
- `ADMIN_EMAIL_FROM_ADDRESS`
- `ADMIN_EMAIL_FROM_NAME`

## Dokumentasi

Dokumentasi utama proyek tersedia di folder [docs](docs):

- [Laporan Eksekutif](docs/LAPORAN_EKSEKUTIF.md)
- [Laporan Akhir Proyek](docs/LAPORAN_AKHIR_PROYEK.md)
- [ERD Database](docs/ERD_DATABASE.md)
- [Use Case Diagram](docs/USE_CASE_DIAGRAM.md)
- [Workflow Diagram](docs/WORKFLOW_DIAGRAM.md)
- [Bab I - Pendahuluan](docs/laporan_formal/BAB_1_PENDAHULUAN.md)
- [Bab II - Landasan Teori](docs/laporan_formal/BAB_2_LANDASAN_TEORI.md)
- [Bab III - Analisis dan Perancangan](docs/laporan_formal/BAB_3_ANALISIS_DAN_PERANCANGAN.md)
- [Bab IV - Implementasi dan Pengujian](docs/laporan_formal/BAB_4_IMPLEMENTASI_DAN_PENGUJIAN.md)
- [Bab V - Kesimpulan dan Saran](docs/laporan_formal/BAB_5_KESIMPULAN_DAN_SARAN.md)

## Alur Utama

1. Subcon / HRGA membuat pengajuan atau data masuk dari Google Form.
2. Sistem menempatkan pengajuan ke status awal yang sesuai.
3. Paramedic melakukan verifikasi MCU.
4. TOD menambahkan hasil tes teori dan praktek.
5. SHE melakukan review akhir.
6. Jika disetujui, sistem menyimpan hasil dan menyiapkan notifikasi email serta tautan berkas Google Drive.
7. Jika perlu revisi, status dikembalikan ke `pending_hrga` untuk perbaikan.

## Role yang Didukung

- `admin`
- `she`
- `hrga`
- `tod`
- `paramedic`
- `subcon`

## Instalasi Singkat

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

## Validasi & CI

Perubahan terakhir sudah disiapkan untuk rilis dan pipeline GitHub Actions.

- `php artisan test`
- `vendor/bin/pint --test`
- `npm run build`

GitHub Actions saat ini menjalankan PHP 8.3, Node 22, `composer validate`, `composer install`, `npm ci`, migrasi SQLite in-memory, test suite, style check, analisis statis bila tersedia, dan build aset.

Migrasi yang bergantung pada sintaks MySQL dijaga tetap aman saat dijalankan di SQLite in-memory agar pipeline CI tetap konsisten.

## Catatan

- Quick Access Login hanya untuk kebutuhan demo/pengujian.
- Pengiriman email workflow berjalan melalui integrasi Gmail yang telah dikonfigurasi.
- Penolakan pada alur verifikasi tidak menjadi terminal akhir; pengajuan dapat kembali ke tahap revisi.