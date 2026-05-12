<?php
$success = session('success');
$error = session('error');

$totalCategories = count($categories ?? []);
$categoriesWithActivePengajuan = 0;
$categoriesWithMonitoringActive = 0;
$categoriesWithSpreadsheet = 0;
$totalChecklistRows = 0;
$totalChecklistChecked = 0;

foreach (($categories ?? []) as $category) {
    $categoryId = (int) ($category['id'] ?? 0);
    $activePengajuanCount = (int) ($category['active_pengajuan_count'] ?? 0);
    $activeMonitoringCount = (int) ($category['active_monitoring_count'] ?? 0);

    if ($activePengajuanCount > 0) {
        $categoriesWithActivePengajuan++;
    }

    if ($activeMonitoringCount > 0) {
        $categoriesWithMonitoringActive++;
    }

    $sheetSummary = $spreadsheetByCategoryId[$categoryId] ?? null;
    if (is_array($sheetSummary) && (bool) ($sheetSummary['has_file'] ?? false)) {
        $categoriesWithSpreadsheet++;
        $totalChecklistRows += (int) ($sheetSummary['total_rows'] ?? 0);
        $totalChecklistChecked += (int) ($sheetSummary['checked_rows'] ?? 0);
    }
}

$checklistCompletion = $totalChecklistRows > 0
    ? (int) round(($totalChecklistChecked / $totalChecklistRows) * 100)
    : 0;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Monitoring Spreadsheet | LCM</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=3.4">
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
    <a class="is-active" href="<?= e(route('admin.monitoring.php')) ?>">Monitoring Spreadsheet</a>
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

<main id="main-content" class="container page-main">
    <?php if ($success): ?>
        <div class="alert alert-success" data-auto-dismiss="4500"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="card card-elevated">
        <p class="section-eyebrow">Monitoring Admin</p>
        <h1 class="panel-title">Monitoring dari Spreadsheet + Form Monitoring</h1>
        <p class="panel-subtitle">Akses monitoring hanya untuk admin login. Sistem membaca file spreadsheet per kategori untuk checklist otomatis.</p>
        <div class="enterprise-note admin-link-guide" role="note">
            Simpan file spreadsheet di folder <strong>storage/app/private/monitoring</strong> dengan nama sesuai kode kategori, contoh:
            <strong>pengajuan_commisioning.xlsx</strong>.
            Header yang direkomendasikan: <strong>nama</strong>, <strong>status</strong>, <strong>checked</strong>.
        </div>
    </section>

    <section class="grid cols-3 admin-metrics-grid">
        <article class="card card-accent">
            <h3>Kategori Aktif</h3>
            <p class="metric-value"><?= e((string) $totalCategories) ?></p>
            <p class="metric-help">Total kategori aktif pada portal.</p>
        </article>
        <article class="card card-accent">
            <h3>Monitoring Aktif</h3>
            <p class="metric-value"><?= e((string) $categoriesWithMonitoringActive) ?>/<?= e((string) $categoriesWithActivePengajuan) ?></p>
            <p class="metric-help">Kategori berpengajuan yang sudah punya monitoring aktif.</p>
        </article>
        <article class="card card-accent">
            <h3>Spreadsheet Terdeteksi</h3>
            <p class="metric-value"><?= e((string) $categoriesWithSpreadsheet) ?>/<?= e((string) $totalCategories) ?></p>
            <p class="metric-help">Jumlah kategori yang file spreadsheet-nya sudah ditemukan.</p>
        </article>
        <article class="card card-quiet">
            <h3>Checklist Completion</h3>
            <p class="metric-value"><?= e((string) $checklistCompletion) ?>%</p>
            <p class="metric-help"><?= e((string) $totalChecklistChecked) ?> checked dari <?= e((string) $totalChecklistRows) ?> baris monitoring.</p>
        </article>
    </section>

    <section class="card table-card">
        <div class="toolbar">
            <div>
                <h2 class="panel-title">Matriks Monitoring per Kategori</h2>
                <p class="small">Form monitoring dibuka lewat route internal admin agar akses publik tidak bisa langsung membuka menu monitoring.</p>
            </div>
        </div>

        <section class="table-wrap">
            <table>
                <caption class="small table-caption">Status monitoring dan checklist spreadsheet per kategori aktif.</caption>
                <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Status Monitoring</th>
                    <th>Akses Form Monitoring</th>
                    <th>Spreadsheet</th>
                    <th>Checklist Spreadsheet</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$categories): ?>
                    <tr>
                        <td colspan="5"><div class="empty-state"><div class="empty-state-icon">i</div><p>Tidak ada kategori aktif.</p></div></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $categoryId = (int) ($category['id'] ?? 0);
                        $activePengajuanCount = (int) ($category['active_pengajuan_count'] ?? 0);
                        $activeMonitoringCount = (int) ($category['active_monitoring_count'] ?? 0);
                        $activeMonitoringFormId = (int) ($category['active_monitoring_form_id'] ?? 0);
                        $sheetSummary = $spreadsheetByCategoryId[$categoryId] ?? [
                            'has_file' => false,
                            'error' => null,
                            'total_rows' => 0,
                            'checked_rows' => 0,
                            'pending_rows' => 0,
                            'completion_percentage' => 0,
                            'sample_rows' => [],
                            'expected_files' => [],
                        ];

                        $sheetHasFile = (bool) ($sheetSummary['has_file'] ?? false);
                        $sheetHasError = trim((string) ($sheetSummary['error'] ?? '')) !== '';
                        $sheetTotalRows = (int) ($sheetSummary['total_rows'] ?? 0);
                        $sheetCheckedRows = (int) ($sheetSummary['checked_rows'] ?? 0);
                        $sheetCompletion = (int) ($sheetSummary['completion_percentage'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <p><strong><?= e((string) ($category['name'] ?? '-')) ?></strong></p>
                                <p class="small">Kode: <?= e((string) ($category['code'] ?? '-')) ?></p>
                            </td>
                            <td>
                                <?php if ($activePengajuanCount > 0 && $activeMonitoringCount > 0): ?>
                                    <span class="badge badge-checklist is-ready">Siap monitoring</span>
                                <?php elseif ($activePengajuanCount > 0): ?>
                                    <span class="badge badge-checklist is-warning">Belum ada monitoring aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-checklist is-neutral">Tidak ada pengajuan aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($activeMonitoringFormId > 0): ?>
                                    <a class="btn btn-primary btn-sm" href="<?= e(route('admin.monitoring.forms.open', ['id' => $activeMonitoringFormId])) ?>" target="_blank" rel="noopener noreferrer">Buka Monitoring</a>
                                <?php else: ?>
                                    <p class="small">Belum tersedia form monitoring aktif.</p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$sheetHasFile): ?>
                                    <p class="small">Belum ada file spreadsheet untuk kategori ini.</p>
                                    <?php $expectedFiles = $sheetSummary['expected_files'] ?? []; ?>
                                    <?php if ($expectedFiles): ?>
                                        <p class="small monitoring-sheet-meta">Nama file yang didukung: <?= e((string) implode(', ', $expectedFiles)) ?></p>
                                    <?php endif; ?>
                                <?php elseif ($sheetHasError): ?>
                                    <p class="small"><?= e((string) ($sheetSummary['error'] ?? 'Gagal membaca file.')) ?></p>
                                <?php else: ?>
                                    <p class="small monitoring-sheet-meta">File: <?= e((string) ($sheetSummary['file_name'] ?? '-')) ?></p>
                                    <p class="small monitoring-sheet-meta">Path: <?= e((string) ($sheetSummary['relative_path'] ?? '-')) ?></p>
                                    <p class="small monitoring-sheet-meta">Updated: <?= e((string) ($sheetSummary['last_modified_at'] ?? '-')) ?></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sheetHasFile && !$sheetHasError): ?>
                                    <?php if ($sheetTotalRows > 0): ?>
                                        <?php if ($sheetCompletion >= 100): ?>
                                            <span class="badge badge-checklist is-ready">Checklist selesai 100%</span>
                                        <?php elseif ($sheetCompletion > 0): ?>
                                            <span class="badge badge-checklist is-warning">Checklist <?= e((string) $sheetCompletion) ?>%</span>
                                        <?php else: ?>
                                            <span class="badge badge-checklist is-neutral">Belum ada baris checked</span>
                                        <?php endif; ?>

                                        <p class="small monitoring-sheet-meta mt-sm">Checked <?= e((string) $sheetCheckedRows) ?> / <?= e((string) $sheetTotalRows) ?> baris</p>

                                        <?php $sampleRows = $sheetSummary['sample_rows'] ?? []; ?>
                                        <?php if ($sampleRows): ?>
                                            <ul class="monitoring-sample-list">
                                                <?php foreach ($sampleRows as $sample): ?>
                                                    <li>
                                                        <span><?= e((string) ($sample['label'] ?? '-')) ?></span>
                                                        <span class="badge badge-checklist <?= (bool) ($sample['checked'] ?? false) ? 'is-ready' : 'is-neutral' ?>">
                                                            <?= e((string) ($sample['status'] ?? ((bool) ($sample['checked'] ?? false) ? 'Checked' : 'Pending'))) ?>
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-checklist is-neutral">Spreadsheet kosong</span>
                                    <?php endif; ?>
                                <?php elseif ($sheetHasError): ?>
                                    <span class="badge badge-checklist is-warning">Perlu perbaikan format file</span>
                                <?php else: ?>
                                    <span class="badge badge-checklist is-neutral">Belum ada spreadsheet</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </section>
</main>
<script src="<?= e(asset('assets/app.js')) ?>?v=3.4"></script>
</body>
</html>
