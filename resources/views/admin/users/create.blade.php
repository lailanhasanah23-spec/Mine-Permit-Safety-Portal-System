<?php
$error = session('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Tambah Pengguna | LCM</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=3.4">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.0">
</head>
<body>
<header class="topbar topbar-admin">
    <div class="container">
        <a class="brand brand-with-logo" href="<?= e(route('admin.dashboard.php')) ?>">
            <img class="brand-logo" src="<?= e(asset('assets/branding/remote/lcm-logo.png')) ?>" alt="Logo Laz Coal Mandiri" width="52" height="52">
            <span class="brand-text">
                <span class="brand-kicker">Konsol Administrator</span>
                <span class="brand-title">SHE APPS Safety Portal</span>
            </span>
        </a>
    </div>
</header>

<main class="container page-main" style="max-width: 600px;">
    <div class="mb-lg">
        <a href="<?= e(route('admin.users.index')) ?>" class="btn btn-ghost">&larr; Kembali ke Daftar Pengguna</a>
    </div>

    <section class="card card-elevated">
        <h1 class="panel-title mb-md">Tambah Pengguna Baru</h1>
        <p class="mb-lg text-gray-600">Buat akun untuk administrator atau role khusus. Pengguna akan diminta mengubah kata sandi pada saat login pertama kali.</p>

        <?php if ($errors->any()): ?>
            <div class="alert alert-error mb-md">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors->all() as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= e(route('admin.users.store')) ?>" method="post">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group mb-md">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" class="form-control" value="<?= e(old('full_name')) ?>" required maxlength="120" placeholder="Contoh: Budi Santoso">
            </div>

            <div class="form-group mb-md">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e(old('email')) ?>" required maxlength="255" placeholder="Contoh: budi@lcm.co.id">
            </div>

            <div class="form-group mb-md">
                <label for="role">Role / Peran</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Super Admin (Full Access)</option>
                    <option value="she" <?= old('role') === 'she' ? 'selected' : '' ?>>SHE (Safety Health Environment)</option>
                    <option value="hrga" <?= old('role') === 'hrga' ? 'selected' : '' ?>>HRGA (Human Resources & GA)</option>
                    <option value="tod" <?= old('role') === 'tod' ? 'selected' : '' ?>>TOD (Training & Operational Dept)</option>
                </select>
            </div>

            <div class="form-group mb-md">
                <label for="password">Kata Sandi Sementara</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="Minimal 8 karakter">
            </div>

            <div class="form-group mb-md">
                <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8">
            </div>

            <div class="form-group mb-lg">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= old('is_active', '1') ? 'checked' : '' ?>>
                    <span>Akun Aktif (Dapat digunakan untuk login)</span>
                </label>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary w-full">Simpan Pengguna</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
