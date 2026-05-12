<?php
$error = session('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Edit Pengguna | LCM</title>
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
        <h1 class="panel-title mb-md">Edit Pengguna</h1>
        <p class="mb-lg text-gray-600">Perbarui profil atau ubah role pengguna. Jika Anda mengisi kolom kata sandi, maka kata sandi pengguna akan direset.</p>

        <?php if ($errors->any()): ?>
            <div class="alert alert-error mb-md">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors->all() as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= e(route('admin.users.update', $user->id)) ?>" method="post">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="form-group mb-md">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" class="form-control" value="<?= e(old('full_name', $user->full_name)) ?>" required maxlength="120">
            </div>

            <div class="form-group mb-md">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e(old('email', $user->email)) ?>" required maxlength="255">
            </div>

            <div class="form-group mb-md">
                <label for="role">Role / Peran</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="admin" <?= old('role', $user->role) === 'admin' ? 'selected' : '' ?>>Super Admin (Full Access)</option>
                    <option value="she" <?= old('role', $user->role) === 'she' ? 'selected' : '' ?>>SHE (Safety Health Environment)</option>
                    <option value="hrga" <?= old('role', $user->role) === 'hrga' ? 'selected' : '' ?>>HRGA (Human Resources & GA)</option>
                    <option value="tod" <?= old('role', $user->role) === 'tod' ? 'selected' : '' ?>>TOD (Training & Operational Dept)</option>
                </select>
            </div>

            <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">

            <p class="small font-bold mb-sm text-gray-600">Ganti Kata Sandi (Opsional)</p>
            <p class="small text-gray-500 mb-md">Biarkan kosong jika tidak ingin mengubah kata sandi.</p>

            <div class="form-group mb-md">
                <label for="password">Kata Sandi Baru</label>
                <input type="password" id="password" name="password" class="form-control" minlength="8">
            </div>

            <div class="form-group mb-md">
                <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8">
            </div>

            <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">

            <div class="form-group mb-lg">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= old('is_active', $user->is_active) ? 'checked' : '' ?>>
                    <span>Akun Aktif (Dapat digunakan untuk login)</span>
                </label>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary w-full">Simpan Perubahan</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
