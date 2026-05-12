# Pengaturan Pengembangan (Laragon)

Langkah cepat untuk menyiapkan lingkungan pengembangan di Laragon / Windows.

1. Pastikan Laragon terpasang dan `php`, `composer`, `npm` tersedia di PATH.
2. Salin `.env.example` menjadi `.env` dan atur konfigurasi DB jika perlu.

```powershell
copy .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate
npm run dev
```

Jika menggunakan SQLite untuk pengembangan lokal (direkomendasikan cepat):

```powershell
setx DB_CONNECTION sqlite
setx DB_DATABASE "%CD%\\database\\database.sqlite"
php artisan migrate
```

Perintah penting untuk verifikasi:

- `php artisan test` — jalankan test suite
- `./vendor/bin/phpstan analyse` — static analysis (jika terpasang)
- `./vendor/bin/psalm` — static analysis (jika terpasang)
- `npm run build` — build assets

Jika Anda menggunakan Laragon dengan Virtual Host, pastikan `APP_URL` di `.env` menyesuaikan.
