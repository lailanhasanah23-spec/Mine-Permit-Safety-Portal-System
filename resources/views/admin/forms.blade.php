<?php
$success = session('success');
$error = session('error');
$scheduleConflictContext = session('schedule_conflict_context', []);
$scheduleConflictDetails = session('schedule_conflict_details', []);
$editedFormId = (int) session('edited_form_id', 0);
$editedFormUrl = (string) session('edited_form_url', '');
$createdFormId = (int) session('created_form_id', 0);
$createdFormUrl = (string) session('created_form_url', '');

function admin_format_effective_date(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '-';
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $value);
    if (!$date) {
        return (string) $value;
    }

    return $date->format('d M Y');
}

function admin_format_datetime(?string $value): string
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

function admin_form_window_badge(array $form, string $today): array
{
    if ((int) ($form['is_active'] ?? 0) !== 1) {
        return ['label' => 'Nonaktif', 'class' => 'is-inactive', 'key' => 'inactive'];
    }

    $start = (string) ($form['effective_start'] ?? '');
    $end = (string) ($form['effective_end'] ?? '');

    if ($start !== '' && $today < $start) {
        return ['label' => 'Terjadwal', 'class' => 'is-scheduled', 'key' => 'scheduled'];
    }

    if ($end !== '' && $today > $end) {
        return ['label' => 'Kedaluwarsa', 'class' => 'is-expired', 'key' => 'expired'];
    }

    return ['label' => 'Aktif Hari Ini', 'class' => 'is-live', 'key' => 'live'];
}

function admin_form_scope_badge(array $form): array
{
    $scope = (string) ($form['link_scope'] ?? 'public');
    if ($scope === 'private') {
        return ['label' => 'Private', 'class' => 'is-private'];
    }

    return ['label' => 'Public', 'class' => 'is-public'];
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Kelola Formulir Administrator | LCM</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=3.14">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.0">
</head>
<body class="admin-forms-page">
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
    <a class="is-active" href="<?= e(route('admin.forms.php')) ?>">Kelola Formulir</a>
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

<main id="main-content" class="container page-main">
    <?php if ($success): ?>
        <div class="alert alert-success" data-auto-dismiss="4500" data-saved-form-id="<?= e((string) max($editedFormId, $createdFormId)) ?>">
            <div>
                <p><?= e($success) ?></p>
                <?php if ($editedFormId > 0): ?>
                    <p class="small saved-form-meta">Perubahan tersimpan pada Form ID #<?= e((string) $editedFormId) ?>.</p>
                <?php elseif ($createdFormId > 0): ?>
                    <p class="small saved-form-meta">Form baru tersimpan dengan ID #<?= e((string) $createdFormId) ?>.</p>
                <?php endif; ?>

                <?php $savedUrl = $editedFormId > 0 ? $editedFormUrl : $createdFormUrl; ?>
                <?php if (trim($savedUrl) !== ''): ?>
                    <p class="small saved-form-meta">URL tersimpan: <?= e($savedUrl) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (is_array($scheduleConflictDetails) && $scheduleConflictDetails !== []): ?>
        <?php
        $requestedPurpose = (string) ($scheduleConflictContext['purpose'] ?? '');
        $requestedStart = (string) ($scheduleConflictContext['effective_start'] ?? '');
        $requestedEnd = (string) ($scheduleConflictContext['effective_end'] ?? '');
        $requestedPurposeLabel = $requestedPurpose === 'monitoring' ? 'Monitoring' : 'Pengajuan';
        ?>
        <section class="card card-quiet">
            <h3>Detail Bentrok Jadwal</h3>
            <p class="small">
                Sistem mendeteksi <?= e((string) count($scheduleConflictDetails)) ?> formulir aktif yang bertabrakan.
                Tujuan pengajuan saat ini: <strong><?= e($requestedPurposeLabel) ?></strong>,
                periode: <strong><?= e(admin_format_effective_date($requestedStart)) ?> - <?= e(admin_format_effective_date($requestedEnd)) ?></strong>.
            </p>

            <div class="table-wrap mt-sm">
                <table>
                    <thead>
                    <tr>
                        <th>ID &amp; Formulir</th>
                        <th>Kategori</th>
                        <th>Tujuan</th>
                        <th>Scope</th>
                        <th>Periode Aktif</th>
                        <th>Terakhir Diubah</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($scheduleConflictDetails as $conflict): ?>
                        <?php
                        $conflictId = (int) ($conflict['id'] ?? 0);
                        $conflictPurpose = (string) ($conflict['purpose'] ?? 'pengajuan');
                        $conflictScope = (string) ($conflict['link_scope'] ?? 'public');
                        $conflictUrl = (string) ($conflict['form_url'] ?? '');
                        ?>
                        <tr>
                            <td>
                                <p><strong>#<?= e((string) $conflictId) ?> - <?= e((string) ($conflict['title'] ?? '-')) ?></strong></p>
                                <?php if ($conflictId > 0): ?>
                                    <p class="small"><a href="#form-row-<?= e((string) $conflictId) ?>">Loncat ke baris formulir</a></p>
                                <?php endif; ?>
                            </td>
                            <td><?= e((string) ($conflict['category_name'] ?? '-')) ?></td>
                            <td><?= e($conflictPurpose === 'monitoring' ? 'Monitoring' : 'Pengajuan') ?></td>
                            <td><?= e($conflictScope === 'private' ? 'Private' : 'Public') ?></td>
                            <td><?= e(admin_format_effective_date((string) ($conflict['effective_start'] ?? ''))) ?> - <?= e(admin_format_effective_date((string) ($conflict['effective_end'] ?? ''))) ?></td>
                            <td><?= e(admin_format_datetime((string) ($conflict['updated_at'] ?? ''))) ?></td>
                            <td>
                                <?php if (trim($conflictUrl) !== ''): ?>
                                    <a href="<?= e($conflictUrl) ?>" target="_blank" rel="noopener noreferrer">Buka URL</a>
                                <?php else: ?>
                                    <span class="small">URL kosong</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <section class="card card-elevated">
        <p class="section-eyebrow">Operasional Formulir</p>
        <h1 class="panel-title">Kelola Formulir Google</h1>
        <p class="panel-subtitle">Kelola seluruh tautan formulir pengajuan dan monitoring dari satu layar operasional.</p>
        <div class="enterprise-note admin-link-guide" role="note">
            <strong>Edit link Google Form sekarang lebih cepat:</strong>
            Klik tombol <strong>Edit Form</strong> pada kolom aksi untuk membuka popup editor, ubah kolom <strong>URL Formulir</strong>, lalu klik <strong>Simpan Perubahan</strong>. Sistem akan memvalidasi dan menormalisasi URL Google Form secara aman sebelum disimpan.
        </div>
    </section>

    <section id="adminSecondaryPanels" class="admin-secondary-panels" data-admin-secondary-panels>
    <section class="grid cols-3 admin-metrics-grid">
        <article class="card card-accent">
            <h3>Total Formulir</h3>
            <p class="metric-value"><?= e((string) $totalFormsCount) ?></p>
            <p class="metric-help">Seluruh data formulir yang tersedia di sistem.</p>
        </article>
        <article class="card card-accent">
            <h3>Formulir Aktif</h3>
            <p class="metric-value"><?= e((string) $activeFormsCount) ?></p>
            <p class="metric-help">Ditampilkan pada portal publik.</p>
        </article>
        <article class="card card-quiet">
            <h3>Formulir Nonaktif</h3>
            <p class="metric-value"><?= e((string) $inactiveFormsCount) ?></p>
            <p class="metric-help">Tetap tersimpan sebagai jejak audit.</p>
        </article>
        <article class="card card-accent">
            <h3>Checklist Monitoring</h3>
            <p class="metric-value"><?= e((string) $categoriesMonitoringChecklistReady) ?>/<?= e((string) $categoriesWithActivePengajuan) ?></p>
            <p class="metric-help">Kategori pengajuan yang sudah punya monitoring aktif otomatis terceklis.</p>
            <?php if ($categoriesMonitoringChecklistMissing > 0): ?>
                <p class="small checklist-gap-note">Masih butuh monitoring aktif: <?= e((string) $categoriesMonitoringChecklistMissing) ?> kategori.</p>
            <?php endif; ?>
        </article>
    </section>

    <section class="card card-quiet admin-workflow-guide">
        <h3>Alur Edit URL yang Paling Cepat</h3>
        <ol class="admin-steps">
            <li>Klik tombol <strong>Edit Form</strong> pada baris formulir yang ingin diubah.</li>
            <li>Ubah kolom <strong>URL Formulir</strong>, lalu cek tombol preview jika perlu.</li>
            <li>Klik <strong>Simpan Perubahan</strong>. Saat berhasil, popup tertutup otomatis, baris ditandai <strong>Baru Disimpan</strong>, dan status checklist monitoring otomatis diperbarui.</li>
        </ol>
    </section>
    </section>

    <section class="card card-quiet admin-forms-command">
        <div>
            <h2 class="panel-title">Daftar Formulir Operasional</h2>
            <p class="small">Fokus utama halaman ini adalah pencarian dan pengelolaan formulir. Form tambah ditampilkan saat Anda memilih tombol Tambah Formulir Baru.</p>
        </div>
        <div class="admin-forms-command-actions">
            <button type="button" class="btn btn-primary" data-open-create-form-modal>Tambah Formulir Baru</button>
            <button type="button" class="btn btn-ghost" data-toggle-admin-secondary aria-expanded="true" aria-controls="adminSecondaryPanels">Sembunyikan Ringkasan</button>
        </div>
    </section>

    <section class="admin-forms-stack">
        <article class="card table-card">
            <div class="toolbar">
                <div class="toolbar-head">
                    <h2 class="panel-title">Daftar Formulir</h2>
                    <p class="small">Gunakan pencarian cepat. Tekan "/" untuk langsung fokus ke kolom pencarian.</p>
                </div>
                <div class="toolbar-controls">
                    <input
                        type="search"
                        class="input-search"
                        placeholder="Cari kategori, judul, tujuan, status..."
                        aria-label="Cari formulir"
                        data-filter-input
                        data-filter-target="#formsTableBody tr"
                    >
                    <div class="filter-row mt-sm">
                        <select data-filter-purpose aria-label="Filter tujuan formulir">
                            <option value="">Semua Tujuan</option>
                            <option value="pengajuan">Pengajuan</option>
                            <option value="monitoring">Monitoring</option>
                        </select>
                        <select data-filter-link-scope aria-label="Filter scope akses link">
                            <option value="">Semua Scope</option>
                            <option value="public">Publik</option>
                            <option value="private">Privat</option>
                        </select>
                        <select data-filter-window-status aria-label="Filter status jadwal">
                            <option value="">Semua Status</option>
                            <option value="live">Aktif Hari Ini</option>
                            <option value="scheduled">Terjadwal</option>
                            <option value="expired">Kedaluwarsa</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="filter-row mt-sm">
                        <select data-sort-select aria-label="Urutkan daftar formulir">
                            <option value="updated_desc">Terbaru Diubah</option>
                            <option value="updated_asc">Terlama Diubah</option>
                            <option value="title_asc">Judul A-Z</option>
                            <option value="title_desc">Judul Z-A</option>
                        </select>
                        <button type="button" class="btn btn-secondary" data-filter-reset>Reset Filter</button>
                    </div>
                    <p class="result-count mt-sm" data-filter-count aria-live="polite">0 entri ditampilkan</p>
                </div>
            </div>

            <section class="table-wrap">
                <table class="forms-management-table">
                    <caption class="small table-caption">Data formulir ditampilkan dari konfigurasi aktif pada basis data.</caption>
                    <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Judul Formulir</th>
                        <th>Tujuan</th>
                        <th>Checklist Otomatis</th>
                        <th>Scope</th>
                        <th>URL Tersimpan</th>
                        <th>Periode Aktif</th>
                        <th>Status</th>
                        <th>Terakhir Diubah</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody id="formsTableBody">
                    <?php if (!$forms): ?>
                        <tr>
                            <td colspan="10"><div class="empty-state"><div class="empty-state-icon">i</div><p>Belum ada data formulir. Klik tombol Tambah Formulir Baru untuk membuat data pertama.</p></div></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($forms as $form): ?>
                            <?php $windowBadge = admin_form_window_badge($form, $todayDate); ?>
                            <?php $scopeBadge = admin_form_scope_badge($form); ?>
                            <?php $isRecentlySaved = $editedFormId === (int) $form['id'] || $createdFormId === (int) $form['id']; ?>
                            <?php $categoryId = (int) ($form['category_id'] ?? 0); ?>
                            <?php $checklistState = $categoryMonitoringMap[$categoryId] ?? ['has_active_pengajuan' => false, 'has_active_monitoring' => false]; ?>
                            <?php $hasActivePengajuan = (bool) ($checklistState['has_active_pengajuan'] ?? false); ?>
                            <?php $hasActiveMonitoring = (bool) ($checklistState['has_active_monitoring'] ?? false); ?>
                            <?php $isFormActive = (int) ($form['is_active'] ?? 0) === 1; ?>
                            <tr
                                id="form-row-<?= e((string) $form['id']) ?>"
                                class="<?= $isRecentlySaved ? 'form-row-updated' : '' ?>"
                                data-form-id="<?= e((string) $form['id']) ?>"
                                data-purpose="<?= e((string) $form['purpose']) ?>"
                                data-link-scope="<?= e((string) ($form['link_scope'] ?? 'public')) ?>"
                                data-window-status="<?= e((string) $windowBadge['key']) ?>"
                                data-sort-title="<?= e(mb_strtolower((string) $form['title'])) ?>"
                                data-sort-updated="<?= e((string) strtotime((string) ($form['updated_at'] ?? '1970-01-01 00:00:00'))) ?>"
                            >
                                <td data-label="Kategori"><?= e($form['category_name']) ?></td>
                                <td data-label="Judul Formulir"><?= e($form['title']) ?></td>
                                <td data-label="Tujuan"><?= e($form['purpose'] === 'monitoring' ? 'Monitoring' : 'Pengajuan') ?></td>
                                <td data-label="Checklist Otomatis">
                                    <?php if (!$isFormActive): ?>
                                        <span class="badge badge-checklist is-neutral">Tidak dihitung (nonaktif)</span>
                                    <?php elseif ($form['purpose'] === 'monitoring'): ?>
                                        <?php if ($hasActivePengajuan): ?>
                                            <span class="badge badge-checklist is-ready">Checklist aktif untuk pengajuan kategori ini</span>
                                        <?php else: ?>
                                            <span class="badge badge-checklist is-warning">Belum ada pengajuan aktif pada kategori ini</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($hasActiveMonitoring): ?>
                                            <span class="badge badge-checklist is-ready">Terceklis otomatis saat dimonitor</span>
                                        <?php else: ?>
                                            <span class="badge badge-checklist is-warning">Monitoring aktif belum tersedia</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Scope">
                                    <span class="badge badge-scope <?= e($scopeBadge['class']) ?>"><?= e($scopeBadge['label']) ?></span>
                                </td>
                                <td data-label="URL Tersimpan">
                                    <div class="form-link-cell">
                                        <a href="<?= e($form['form_url']) ?>" target="_blank" rel="noopener noreferrer">Buka Tautan</a>
                                        <button
                                            type="button"
                                            class="btn btn-ghost btn-sm"
                                            data-copy-form-url-value="<?= e($form['form_url']) ?>"
                                            data-copy-form-url
                                        >
                                            Salin URL
                                        </button>
                                        <?php if (!empty($form['gdrive_folder_id'])): ?>
                                        <a href="<?= e(route('admin.drive-explorer', ['folder_id' => $form['gdrive_folder_id']])) ?>" class="btn btn-ghost btn-sm" title="Buka folder Drive form ini" style="color:var(--admin-primary);">
                                            📁 Folder Drive
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Periode Aktif">
                                    <p class="schedule-range"><?= e(admin_format_effective_date($form['effective_start'])) ?> - <?= e(admin_format_effective_date($form['effective_end'])) ?></p>
                                </td>
                                <td data-label="Status">
                                    <span class="badge badge-window <?= e($windowBadge['class']) ?>">
                                        <?= e($windowBadge['label']) ?>
                                    </span>
                                </td>
                                <td data-label="Terakhir Diubah">
                                    <span class="small"><?= e(admin_format_datetime((string) ($form['updated_at'] ?? ''))) ?></span>
                                    <?php if ($isRecentlySaved): ?>
                                        <p class="small saved-row-tag">Baru Disimpan</p>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Aksi">
                                    <div class="table-action-stack">
                                        <button
                                            type="button"
                                            class="btn btn-secondary btn-sm form-link-edit-btn"
                                            data-open-form-editor
                                            data-open-form-editor-id="<?= e((string) $form['id']) ?>"
                                            data-form-id="<?= e((string) $form['id']) ?>"
                                            data-form-category="<?= e((string) ($form['category_name'] ?? '-')) ?>"
                                            data-form-title="<?= e((string) ($form['title'] ?? '')) ?>"
                                            data-form-purpose="<?= e((string) ($form['purpose'] ?? 'pengajuan')) ?>"
                                            data-form-link-scope="<?= e((string) ($form['link_scope'] ?? 'public')) ?>"
                                            data-form-url="<?= e((string) ($form['form_url'] ?? '')) ?>"
                                            data-form-gdrive-folder-id="<?= e((string) ($form['gdrive_folder_id'] ?? '')) ?>"
                                            data-form-effective-start="<?= e((string) ($form['effective_start'] ?? '')) ?>"
                                            data-form-effective-end="<?= e((string) ($form['effective_end'] ?? '')) ?>"
                                            data-form-notes="<?= e((string) ($form['notes'] ?? '')) ?>"
                                            data-form-is-active="<?= (int) ($form['is_active'] ?? 0) === 1 ? '1' : '0' ?>"
                                        >
                                            Edit Form
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <p class="small is-hidden mt-sm" data-filter-empty>Tidak ada formulir yang sesuai dengan kata kunci pencarian.</p>
        </article>
    </section>

    <section class="admin-form-editor-modal admin-create-form-modal is-hidden" data-create-form-modal aria-hidden="true">
        <div class="admin-form-editor-backdrop" data-close-create-form-modal></div>
        <div class="admin-form-editor-dialog" role="dialog" aria-modal="true" aria-labelledby="createFormModalTitle">
            <header class="admin-form-editor-header">
                <div>
                    <p class="section-eyebrow">Tambah Data</p>
                    <h3 id="createFormModalTitle">Tambah Formulir Baru</h3>
                    <p class="small">Isi data formulir baru. Sistem akan memvalidasi URL dan mencegah jadwal aktif yang bertabrakan.</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" data-close-create-form-modal>Tutup</button>
            </header>

            <div class="admin-form-editor-body">
                <form method="post" class="grid cols-2 admin-form-editor-grid" data-gform-form data-create-form-modal-form>
                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label for="create_category_id">Kategori</label>
                        <select id="create_category_id" name="category_id" required>
                            <option value="">Pilih kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e((string) $category['id']) ?>"><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_purpose">Tujuan Formulir</label>
                        <select id="create_purpose" name="purpose" required>
                            <option value="pengajuan">Pengajuan</option>
                            <option value="monitoring">Monitoring</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_link_scope">Scope Akses Link</label>
                        <select id="create_link_scope" name="link_scope" required data-form-link-scope>
                            <option value="public">Publik (dapat diakses responden)</option>
                            <option value="private">Privat (internal, butuh akun terautorisasi)</option>
                        </select>
                    </div>

                    <div class="form-group col-span-full">
                        <label for="create_title">Judul Formulir</label>
                        <input id="create_title" name="title" maxlength="190" required>
                    </div>

                    <div class="form-group col-span-full">
                        <label for="create_form_url">URL Google Form</label>
                        <input id="create_form_url" name="form_url" type="url" placeholder="https://docs.google.com/forms/..." maxlength="500" required data-form-url-input>
                        <a class="small url-preview is-hidden" href="#" target="_blank" rel="noopener noreferrer" data-form-url-preview>Lihat preview tautan</a>
                        <p class="small url-hint" data-form-url-hint>Domain yang diizinkan: docs.google.com dan forms.gle. Link edit/formresponse akan otomatis dikonversi ke viewform.</p>
                    </div>

                    <div class="form-group col-span-full">
                        <label for="create_gdrive_folder_id">Folder Google Drive <span style="font-weight:400;color:var(--admin-text-muted);">(Opsional)</span></label>
                        <input id="create_gdrive_folder_id" name="gdrive_folder_id" type="text" placeholder="Tempel URL folder Drive atau ID folder langsung" maxlength="500">
                        <p class="small url-hint">Contoh: <code>https://drive.google.com/drive/folders/1aBcD...</code> — sistem akan mengekstrak ID secara otomatis.</p>
                    </div>

                    <div class="form-group">
                        <label for="create_effective_start">Tanggal Mulai Aktif</label>
                        <input id="create_effective_start" name="effective_start" type="date">
                    </div>

                    <div class="form-group">
                        <label for="create_effective_end">Tanggal Akhir Aktif</label>
                        <input id="create_effective_end" name="effective_end" type="date">
                    </div>

                    <div class="form-group col-span-full">
                        <label for="create_notes">Catatan</label>
                        <textarea id="create_notes" name="notes" rows="3" maxlength="5000" placeholder="Opsional, untuk kebutuhan tim administrator"></textarea>
                    </div>

                    <div class="col-span-full enterprise-note" role="note">
                        Kebijakan enterprise: untuk kombinasi kategori + tujuan, sistem hanya mengizinkan satu formulir aktif dalam periode tanggal yang saling overlap.
                        Untuk scope private, gunakan link docs.google.com/.../viewform agar kontrol akses akun tetap terjaga.
                    </div>

                    <div class="col-span-full admin-form-editor-actions">
                        <button class="btn btn-primary" type="submit">Simpan Formulir</button>
                        <button type="button" class="btn btn-ghost" data-close-create-form-modal>Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="admin-form-editor-modal is-hidden" data-form-editor-modal aria-hidden="true">
        <div class="admin-form-editor-backdrop" data-close-form-editor-modal></div>
        <div class="admin-form-editor-dialog" role="dialog" aria-modal="true" aria-labelledby="formEditorModalTitle">
            <header class="admin-form-editor-header">
                <div>
                    <p class="section-eyebrow">Editor Formulir</p>
                    <h3 id="formEditorModalTitle">Edit Link / Formulir</h3>
                    <p class="small" data-form-editor-modal-meta>Siap mengubah formulir terpilih.</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" data-close-form-editor-modal>Tutup</button>
            </header>

            <div class="admin-form-editor-body">
                <form method="post" class="grid cols-2 admin-form-editor-grid" data-gform-form data-form-editor-modal-form>
                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="" data-modal-field-id>

                    <div class="col-span-full admin-form-editor-kicker">
                        <span class="small">Kategori: <strong data-modal-field-category>-</strong></span>
                    </div>

                    <p class="small form-editor-tip col-span-full">
                        Tips: tempel URL Google Form atau Google Spreadsheet (untuk monitoring). Sistem otomatis menormalisasi ke format aman sebelum disimpan.
                    </p>

                    <div class="form-group col-span-full">
                        <label for="modal_title">Judul Formulir</label>
                        <input id="modal_title" name="title" value="" required data-modal-field-title>
                    </div>

                    <div class="form-group">
                        <label for="modal_purpose">Tujuan</label>
                        <select id="modal_purpose" name="purpose" data-modal-field-purpose>
                            <option value="pengajuan">Pengajuan</option>
                            <option value="monitoring">Monitoring</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_link_scope">Scope Akses Link</label>
                        <select id="modal_link_scope" name="link_scope" data-form-link-scope data-modal-field-link-scope>
                            <option value="public">Publik (responden)</option>
                            <option value="private">Privat (internal)</option>
                        </select>
                    </div>

                    <div class="form-group col-span-full">
                        <label for="modal_form_url">URL Formulir</label>
                        <input id="modal_form_url" name="form_url" type="url" value="" maxlength="500" required data-form-url-input data-edit-url-input data-modal-field-url>
                        <a class="small url-preview is-hidden" href="#" target="_blank" rel="noopener noreferrer" data-form-url-preview>Lihat preview tautan</a>
                        <p class="small url-hint" data-form-url-hint>Sistem akan menormalisasi link edit/formresponse ke viewform. Untuk tujuan monitoring, URL Google Spreadsheet /edit juga diperbolehkan.</p>
                        <div class="editor-url-actions">
                            <button type="button" class="btn btn-secondary btn-sm" data-copy-form-url-input>Salin URL Input</button>
                            <button type="button" class="btn btn-ghost btn-sm" data-open-url-from-input>Buka URL Input</button>
                        </div>
                    </div>

                    <div class="form-group col-span-full">
                        <label for="modal_gdrive_folder_id">Folder Google Drive <span style="font-weight:400;color:var(--admin-text-muted);">(Opsional)</span></label>
                        <input id="modal_gdrive_folder_id" name="gdrive_folder_id" type="text" value="" maxlength="500" placeholder="Tempel URL folder Drive atau ID folder langsung" data-modal-field-gdrive-folder-id>
                        <p class="small url-hint">Tempel URL lengkap folder Drive atau ID-nya. Kosongkan jika tidak ada folder yang ditautkan.</p>
                    </div>

                    <div class="form-group">
                        <label for="modal_effective_start">Mulai Aktif</label>
                        <input id="modal_effective_start" name="effective_start" type="date" value="" data-modal-field-effective-start>
                    </div>

                    <div class="form-group">
                        <label for="modal_effective_end">Akhir Aktif</label>
                        <input id="modal_effective_end" name="effective_end" type="date" value="" data-modal-field-effective-end>
                    </div>

                    <div class="form-group col-span-full">
                        <label for="modal_notes">Catatan</label>
                        <textarea id="modal_notes" name="notes" rows="3" maxlength="5000" data-modal-field-notes></textarea>
                    </div>

                    <div class="form-group col-span-full">
                        <label>
                            <input type="checkbox" name="is_active" value="1" data-modal-field-is-active>
                            Formulir aktif
                        </label>
                    </div>

                    <div class="col-span-full admin-form-editor-actions">
                        <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                        <button type="button" class="btn btn-ghost" data-close-form-editor-modal>Batal</button>
                    </div>
                </form>

                <form method="post" data-confirm="Arsipkan formulir ini (nonaktifkan)?" class="admin-form-editor-archive" data-form-editor-archive-form>
                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="" data-modal-archive-id>
                    <button class="btn btn-danger" type="submit">Arsipkan Formulir Ini</button>
                </form>
            </div>
        </div>
    </section>
</main>
<script src="<?= e(asset('assets/app.js')) ?>?v=3.11"></script>
</body>
</html>

