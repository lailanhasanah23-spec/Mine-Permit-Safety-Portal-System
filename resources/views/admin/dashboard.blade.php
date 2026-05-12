<?php

$success = session('success');
// Ensure $admin and $userRole are available (use LegacyAuth to read current admin session)
$admin = App\Support\Legacy\LegacyAuth::user();

if (App\Support\Legacy\LegacyAuth::isVendor()) {
    $userRole = 'subcon';
} else {
    $userId = (int) ($admin['id'] ?? 0);
    try {
        $userRow = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();
        $userRole = (string) ($userRow->role ?? 'admin');
    } catch (\Throwable $e) {
        $userRole = (string) ($admin['role'] ?? 'admin');
    }
}

function dashboard_format_datetime(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '-';
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $value);
    if (!$dt) {
        return (string) $value;
    }

    return $dt->format('d M Y H:i');
}

function dashboard_max_value(array $rows, string $key): int
{
    $max = 0;
    foreach ($rows as $row) {
        $value = (int) ($row[$key] ?? 0);
        if ($value > $max) {
            $max = $value;
        }
    }

    return $max;
}

function dashboard_bar_width(int $value, int $max): string
{
    if ($max <= 0) {
        return '0';
    }

    $percentage = ($value / $max) * 100;
    if ($percentage > 0 && $percentage < 6) {
        $percentage = 6;
    }

    return number_format($percentage, 2, '.', '');
}

function dashboard_expiry_badge_class(int $daysLeft): string
{
    if ($daysLeft <= 1) {
        return 'is-danger';
    }

    if ($daysLeft <= 3) {
        return 'is-warning';
    }

    return 'is-info';
}

function dashboard_heat_class(int $value, int $max): string
{
    if ($max <= 0 || $value === 0) {
        return 'is-calm';
    }

    $ratio = $value / $max;
    if ($ratio >= 0.75) {
        return 'is-high';
    }

    if ($ratio >= 0.4) {
        return 'is-medium';
    }

    return 'is-low';
}

$dailyLiveMax = dashboard_max_value($dailyTrend, 'live_forms');
$dailyConflictMax = dashboard_max_value($dailyTrend, 'conflicts_prevented');
$weeklyLiveMax = dashboard_max_value($weeklyTrend, 'live_forms');
$weeklyConflictMax = dashboard_max_value($weeklyTrend, 'conflicts_prevented');
$heatMax = dashboard_max_value($conflictHeatmap, 'conflicts_prevented');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Dashboard Administrator | LCM</title>
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
    <a class="<?= request()->routeIs('admin.dashboard*') ? 'is-active' : '' ?>" href="<?= e(route('admin.dashboard.php')) ?>">Dashboard</a>
    
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
    <a href="<?= e(route('admin.drive-explorer')) ?>">Google Drive Explorer</a>
    <?php endif; ?>

    <a href="<?= e(route('admin.change-password.php')) ?>">Ubah Kata Sandi</a>
</nav>

<main id="main-content" class="container page-main">
    <?php if ($success): ?>
        <div class="alert alert-success" data-auto-dismiss="3500"><?= e($success) ?></div>
    <?php endif; ?>

    <section class="card card-elevated">
        <p class="section-eyebrow"><?= e($userRole === 'subcon' ? 'Portal Vendor / Subcon' : 'Ringkasan Administrator') ?></p>
        <h1 class="panel-title"><?= e($userRole === 'subcon' ? 'Selamat Datang, '.($admin['full_name'] ?? 'Vendor') : 'Dashboard Operasional Administrator') ?></h1>
        <p class="panel-subtitle"><?= e($userRole === 'subcon' ? 'Kelola dan pantau status pengajuan SIMPER dan Permit kerja Anda dari sini.' : 'Pantau kesehatan data portal dan jalankan pembaruan formulir secara terkontrol tanpa perubahan kode aplikasi.') ?></p>
        <?php if ($userRole === 'subcon'): ?>
        <div class="actions mt-md">
            <a class="btn btn-primary" href="<?= e(route('admin.submissions.create')) ?>">+ Pengajuan Baru</a>
            <a class="btn btn-secondary" href="<?= e(route('admin.submissions.index')) ?>">Lihat Semua Pengajuan Saya</a>
        </div>
        <?php endif; ?>
    </section>

    <?php if (in_array($userRole, ['admin', 'she'], true)): ?>
    <section class="dashboard-kpi-grid">
        <article class="card card-accent kpi-card is-live">
            <p class="kpi-label">Live Forms</p>
            <p class="metric-value"><?= e((string) $stats['live_forms']) ?></p>
            <p class="metric-help">Formulir aktif dan berlaku saat ini di portal publik.</p>
        </article>
        <article class="card card-accent kpi-card is-warning">
            <p class="kpi-label">Expiring Soon (7 Hari)</p>
            <p class="metric-value"><?= e((string) $stats['expiring_soon']) ?></p>
            <p class="metric-help">Formulir aktif yang akan berakhir dalam 7 hari ke depan.</p>
        </article>
        <article class="card card-accent kpi-card is-secure">
            <p class="kpi-label">Conflicts Prevented (30 Hari)</p>
            <p class="metric-value"><?= e((string) $stats['conflicts_prevented_30d']) ?></p>
            <p class="metric-help">Percobaan update jadwal konflik yang berhasil diblokir sistem.</p>
        </article>
        <article class="card card-quiet kpi-card">
            <p class="kpi-label">Sinkronisasi Data Terakhir</p>
            <p class="metric-value metric-value-sm"><?= e(dashboard_format_datetime((string) ($stats['last_form_update_at'] ?? ''))) ?></p>
            <p class="metric-help">Timestamp update data form terakhir di sistem.</p>
        </article>
    </section>

    <section class="dashboard-analytics-grid">
        <article class="card trend-card">
            <div class="trend-head">
                <h3>KPI Trend Harian (7 Hari)</h3>
                <p class="small">Pantau pergerakan live forms dan konflik yang berhasil dicegah setiap hari.</p>
            </div>
            <div class="trend-list">
                <?php foreach ($dailyTrend as $row): ?>
                    <div class="trend-row">
                        <div class="trend-meta">
                            <span class="trend-label"><?= e((string) $row['label']) ?></span>
                            <span class="small">Live <?= e((string) $row['live_forms']) ?> | Conflict <?= e((string) $row['conflicts_prevented']) ?></span>
                        </div>
                        <div class="trend-bars">
                            <span class="trend-bar is-live" style="width: <?= e(dashboard_bar_width((int) $row['live_forms'], $dailyLiveMax)) ?>%"></span>
                            <span class="trend-bar is-conflict" style="width: <?= e(dashboard_bar_width((int) $row['conflicts_prevented'], $dailyConflictMax)) ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="card trend-card">
            <div class="trend-head">
                <h3>KPI Trend Mingguan (8 Minggu)</h3>
                <p class="small">Snapshot mingguan untuk membaca stabilitas operasional dan risiko bentrok jadwal.</p>
            </div>
            <div class="trend-list">
                <?php foreach ($weeklyTrend as $row): ?>
                    <div class="trend-row">
                        <div class="trend-meta">
                            <span class="trend-label"><?= e((string) $row['label']) ?></span>
                            <span class="small">Live <?= e((string) $row['live_forms']) ?> | Conflict <?= e((string) $row['conflicts_prevented']) ?></span>
                        </div>
                        <div class="trend-bars">
                            <span class="trend-bar is-live" style="width: <?= e(dashboard_bar_width((int) $row['live_forms'], $weeklyLiveMax)) ?>%"></span>
                            <span class="trend-bar is-conflict" style="width: <?= e(dashboard_bar_width((int) $row['conflicts_prevented'], $weeklyConflictMax)) ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endif; ?>
    </section>

    <section class="dashboard-ops-grid">
        <?php if (in_array($userRole, ['admin', 'she'], true)): ?>
        <article class="card warning-widget">
            <div class="trend-head">
                <h3>Early Warning: Form Akan Expired</h3>
                <p class="small">Monitoring otomatis untuk formulir aktif yang mendekati batas akhir berlaku.</p>
            </div>

            <?php if (!$expiringForms): ?>
                <div class="enterprise-note" role="note">Tidak ada formulir aktif yang akan berakhir dalam 7 hari ke depan.</div>
            <?php else: ?>
                <div class="warning-list">
                    <?php foreach ($expiringForms as $form): ?>
                        <?php
                        $daysLeft = (int) ($form['days_left'] ?? 0);
                        $daysLabel = $daysLeft <= 0 ? 'Hari ini' : 'D-' . $daysLeft;
                        ?>
                        <div class="warning-item">
                            <div>
                                <p class="warning-title"><?= e((string) $form['title']) ?></p>
                                <p class="small"><?= e((string) $form['category_name']) ?> | <?= e((string) ($form['purpose'] === 'monitoring' ? 'Monitoring' : 'Pengajuan')) ?></p>
                            </div>
                            <div class="warning-meta">
                                <span class="badge badge-window <?= e(dashboard_expiry_badge_class($daysLeft)) ?>"><?= e($daysLabel) ?></span>
                                <span class="small">Berakhir <?= e(dashboard_format_datetime(((string) ($form['effective_end'] ?? '')) . ' 00:00:00')) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="actions mt-md">
                    <a class="btn btn-primary" href="<?= e(route('admin.forms.php')) ?>">Review dan Perbarui Form</a>
                </div>
            <?php endif; ?>
        </article>
        <?php endif; ?>

        <?php if (in_array($userRole, ['admin', 'she'], true)): ?>
        <article class="card heatmap-widget">
            <div class="trend-head">
                <h3>Conflict Heatmap per Kategori</h3>
                <p class="small">Menggambarkan area kategori yang paling sering memicu pencegahan konflik jadwal (30 hari).</p>
            </div>

            <div class="heatmap-list">
                <?php foreach ($conflictHeatmap as $row): ?>
                    <?php
                    $value = (int) ($row['conflicts_prevented'] ?? 0);
                    $heatClass = dashboard_heat_class($value, $heatMax);
                    ?>
                    <div class="heatmap-item <?= e($heatClass) ?>">
                        <div class="heatmap-top">
                            <span><?= e((string) $row['category_name']) ?></span>
                            <strong><?= e((string) $value) ?></strong>
                        </div>
                        <div class="heatmap-track">
                            <span style="width: <?= e(dashboard_bar_width($value, $heatMax)) ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
        <?php endif; ?>
    </section>

    <section class="card mt-lg" style="border-top: 4px solid #3b82f6;">
        <div class="toolbar">
            <div>
                <p class="section-eyebrow">SIMPER &amp; PERMIT Monitoring System</p>
                <h3 class="panel-title">Ringkasan Pengajuan SIMPER &amp; PERMIT</h3>
            </div>
            <div class="actions">
                <a href="<?= e(route('admin.submissions.index')) ?>" class="btn btn-primary btn-sm">Buka Semua Pengajuan</a>
            </div>
        </div>
        
        <div class="grid cols-3 mt-md">
            <div class="pulse-card" style="padding: 1.5rem; text-align: center;">
                <p class="pulse-label">Total Pengajuan</p>
                <p class="pulse-value"><?= e($simperStats['total']) ?></p>
            </div>
            <div class="pulse-card" style="padding: 1.5rem; text-align: center;">
                <p class="pulse-label">Sedang Diproses</p>
                <p class="pulse-value text-warning"><?= e($simperStats['pending']) ?></p>
            </div>
            <div class="pulse-card" style="padding: 1.5rem; text-align: center;">
                <p class="pulse-label">Telah Disetujui</p>
                <p class="pulse-value text-success"><?= e($simperStats['approved']) ?></p>
            </div>
        </div>

        <?php if (!$simperStats['recent']->isEmpty()): ?>
            <div class="table-wrap mt-md">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 8px;">Pemohon</th>
                            <th style="padding: 8px;">Jenis</th>
                            <th style="padding: 8px;">Status</th>
                            <th style="padding: 8px;">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($simperStats['recent'] as $sub): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px;"><strong><?= e($sub->applicant_name) ?></strong></td>
                                <td style="padding: 8px;"><?= e($sub->item_type) ?></td>
                                <td style="padding: 8px;"><span class="badge <?= e($sub->status_color) ?>"><?= e($sub->status_label) ?></span></td>
                                <td style="padding: 8px;"><?= e($sub->created_at->format('d M y')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="grid cols-2 mt-lg">
        <div class="card card-accent">
            <h3>Kategori Aktif</h3>
            <p class="metric-value"><?= e((string) $stats['categories']) ?></p>
            <p class="metric-help">Kategori formulir yang ditampilkan pada portal publik.</p>
        </div>
        <div class="card card-accent">
            <h3>Formulir Aktif</h3>
            <p class="metric-value"><?= e((string) $stats['forms']) ?></p>
            <p class="metric-help">Tautan pengajuan dan monitoring yang saat ini aktif.</p>
        </div>
        <div class="card card-accent">
            <h3>Jumlah SAPKON</h3>
            <p class="metric-value"><?= e((string) $stats['sapkon']) ?></p>
            <p class="metric-help">Perusahaan mitra dengan bucket formulir terpisah.</p>
        </div>
        <?php if (in_array($userRole, ['admin', 'she'], true)): ?>
        <div class="card card-accent">
            <h3>Aksi Utama</h3>
            <p>Tambah, perbarui, dan arsipkan tautan Google Form secara aman dari satu panel kendali.</p>
            <div class="actions mt-md">
                <a class="btn btn-primary" href="<?= e(route('admin.forms.php')) ?>">Buka Kelola Formulir</a>
                <a class="btn btn-primary" href="<?= e(route('admin.email-submissions.php')) ?>">Buka Email SIMPER</a>
                <a class="btn btn-secondary" href="<?= e(route('admin.audit-log.php')) ?>">Lihat Audit Log</a>
                <a class="btn btn-secondary" href="<?= e(route('admin.documents.php')) ?>">Kelola Dokumen</a>
                <a class="btn btn-secondary" href="<?= e(route('admin.change-password.php')) ?>">Perbarui Kata Sandi</a>
            </div>
        </div>
        <?php endif; ?>
    </section>
</main>
<script src="<?= e(asset('assets/app.js')) ?>"></script>
</body>
</html>

