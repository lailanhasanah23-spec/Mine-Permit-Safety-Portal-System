<?php
$success = session('success');
$error = session('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Manajemen Pengguna | LCM</title>
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

    <a href="<?= e(route('admin.change-password.php')) ?>">Ubah Kata Sandi</a>
</nav>

<main class="container page-main">
    <?php if ($success): ?>
        <div class="alert alert-success" data-auto-dismiss="4500"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="card card-elevated" style="background: linear-gradient(to right, #ffffff, #f8fafc); border-left: 5px solid #0f172a;">
        <div class="toolbar">
            <div>
                <p class="section-eyebrow">Pengaturan Sistem</p>
                <h1 class="panel-title" style="font-size: 1.75rem;">Manajemen Pengguna (RBAC)</h1>
                <p class="panel-subtitle">Kelola akses staf operasional (SHE, HRGA, TOD) dan konfigurasi hak akses administrator.</p>
            </div>
            <div class="actions">
                <a href="<?= e(route('admin.users.create')) ?>" class="btn btn-primary" style="box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);">
                    <span>+ Tambah Pengguna Baru</span>
                </a>
            </div>
        </div>
    </section>

    <section class="card table-card mt-lg" style="padding: 0; overflow: hidden; border-radius: 12px;">
        <section class="table-wrap">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th style="padding: 16px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Informasi Akun</th>
                    <th style="padding: 16px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Email</th>
                    <th style="padding: 16px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Peran (Role)</th>
                    <th style="padding: 16px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Status</th>
                    <th style="padding: 16px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Login Terakhir</th>
                    <th style="padding: 16px; text-align: center; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($users->isEmpty()): ?>
                    <tr>
                        <td colspan="6" style="padding: 48px; text-align: center;">
                            <div class="empty-state">
                                <div class="empty-state-icon" style="background: #f1f5f9; color: #94a3b8; font-size: 2rem; margin-bottom: 1rem;">ðŸ‘¥</div>
                                <p style="color: #64748b; font-weight: 500;">Belum ada pengguna terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                            <td style="padding: 16px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #1e293b; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; border: 1px solid #e2e8f0;">
                                        <?= e(strtoupper(substr($user->full_name, 0, 1))) ?>
                                    </div>
                                    <div>
                                        <strong style="display: block; color: #1e293b;"><?= e($user->full_name) ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 16px; color: #64748b; font-size: 0.9rem;"><?= e($user->email) ?></td>
                            <td style="padding: 16px;">
                                <?php
                                $roleClass = match($user->role) {
                                    'admin' => 'is-danger',
                                    'she' => 'is-info',
                                    'hrga' => 'is-warning',
                                    'tod' => 'is-success',
                                    default => 'is-neutral',
                                };
                                ?>
                                <span class="badge <?= e($roleClass) ?>" style="padding: 6px 12px; font-size: 0.7rem;">
                                    <?= e(strtoupper($user->role)) ?>
                                </span>
                            </td>
                            <td style="padding: 16px;">
                                <span class="badge <?= $user->is_active ? 'is-success' : 'is-danger' ?>" style="padding: 4px 10px; font-size: 0.65rem;">
                                    <?= $user->is_active ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td style="padding: 16px; color: #64748b; font-size: 0.85rem;">
                                <?= $user->last_login_at ? e($user->last_login_at->format('d M Y')) : '<span style="opacity: 0.5;">Belum pernah</span>' ?>
                            </td>
                            <td style="padding: 16px; text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <a href="<?= e(route('admin.users.edit', $user->id)) ?>" class="btn btn-secondary btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">Edit</a>
                                    <?php if ($user->id !== ($admin['id'] ?? 0)): ?>
                                        <form action="<?= e(route('admin.users.destroy', $user->id)) ?>" method="post" class="action-inline-form" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 10px; font-size: 0.75rem;">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
        
        <?php if ($users->hasPages()): ?>
            <div class="pagination-container">
                <?= $users->links() ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<script src="<?= e(asset('assets/app.js')) ?>?v=3.4"></script>
</body>
</html>
