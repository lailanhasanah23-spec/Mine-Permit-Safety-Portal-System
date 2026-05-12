<?php
$error = session('error');
$success = session('success');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Ubah Kata Sandi Administrator | LCM</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=2.5">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.0">
</head>
<body>
<a href="#main-content" class="skip-link">Lewati ke konten utama</a>
<header class="topbar topbar-admin">
    <div class="container">
        <a class="brand brand-with-logo" href="<?= e(route('admin.dashboard.php')) ?>">
            <img class="brand-logo" src="<?= e(asset('assets/branding/remote/lcm-logo.png')) ?>" alt="Logo Laz Coal Mandiri" width="52" height="52">
            <span class="brand-text">
                <span class="brand-kicker">Konsol Administrator</span>
                <span class="brand-title">SHE APPS Safety Portal</span>
            </span>
        </a>
        <div class="actions">
            <span class="user-chip" title="Administrator aktif"><?= e((string) ($admin['email'] ?? 'admin')) ?></span>
            <a class="btn btn-ghost" href="<?= e(route('portal.index.php')) ?>">Portal Publik</a>
            <form method="post" action="<?= e(route('admin.logout.php')) ?>" class="action-inline-form">
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                <button class="btn btn-danger" type="submit">Keluar</button>
            </form>
        </div>
    </div>
</header>

<nav class="container quick-nav" aria-label="Navigasi administrator">
    <a href="<?= e(route('admin.dashboard.php')) ?>">Dashboard</a>
    
    <?php if (in_array($userRole, ['admin', 'she'])): ?>
    <a href="<?= e(route('admin.forms.php')) ?>">Kelola Formulir</a>
    <a href="<?= e(route('admin.monitoring.php')) ?>">Monitoring Spreadsheet</a>
    <?php endif; ?>

    <a class="<?= request()->routeIs('admin.submissions*') ? 'is-active' : '' ?>" href="<?= e(route('admin.submissions.index')) ?>">Monitoring Pengajuan</a>
    
    <?php if ($userRole === 'admin'): ?>
    <a class="<?= request()->routeIs('admin.users*') ? 'is-active' : '' ?>" href="<?= e(route('admin.users.index')) ?>">Manajemen Pengguna</a>
    <?php endif; ?>

    <?php if (in_array($userRole, ['admin', 'she'])): ?>
    <a href="<?= e(route('admin.audit-log.php')) ?>">Audit Log</a>
    <a href="<?= e(route('admin.documents.php')) ?>">Dokumen Kebijakan</a>
    <a href="<?= e(route('admin.email-submissions.php')) ?>">Email SIMPER</a>
    <?php endif; ?>

    <a class="is-active" href="<?= e(route('admin.change-password.php')) ?>">Ubah Kata Sandi</a>
</nav>

<main id="main-content" class="container page-main narrow">
    <section class="card card-elevated stack">
        <p class="section-eyebrow">Kebijakan Keamanan</p>
        <h1 class="panel-title">Ubah Kata Sandi Administrator</h1>
        <p class="panel-subtitle">Kebijakan kata sandi: minimal <?= e((string) $minLength) ?> karakter dengan kombinasi huruf dan angka.</p>

        <?php if ($success): ?>
            <div class="alert alert-success" data-auto-dismiss="3500"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <div class="form-group">
                <label for="current_password">Kata Sandi Saat Ini</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
            </div>

            <div class="form-group">
                <label for="new_password">Kata Sandi Baru</label>
                <input id="new_password" name="new_password" type="password" minlength="<?= e((string) $minLength) ?>" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                <input id="confirm_password" name="confirm_password" type="password" minlength="<?= e((string) $minLength) ?>" required autocomplete="new-password">
            </div>

            <div class="enterprise-note" role="note">
                Gunakan kata sandi unik yang tidak digunakan pada sistem lain. Hindari pola sederhana seperti tanggal lahir atau urutan angka.
            </div>

            <div class="actions">
                <button class="btn btn-primary" type="submit">Simpan Kata Sandi Baru</button>
                <a class="btn btn-secondary" href="<?= e(route('admin.dashboard.php')) ?>">Kembali ke Dashboard</a>
            </div>
        </form>
    </section>
</main>

<script src="<?= e(asset('assets/app.js')) ?>"></script>
</body>
</html>

