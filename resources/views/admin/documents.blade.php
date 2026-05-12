<?php
$success = session('success');
$error = session('error');

function document_format_datetime(?string $value): string
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

function document_format_size(int $bytes): string
{
    if ($bytes <= 0) {
        return '-';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $size = (float) $bytes;
    $unitIndex = 0;

    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    return number_format($size, $unitIndex === 0 ? 0 : 2, '.', '') . ' ' . $units[$unitIndex];
}

$documentCode = (string) ($document['code'] ?? 'ktp-ohs-102-mine-permit-simper');

$recentChangeNotes = [];
foreach ($revisions as $revisionRow) {
    $note = trim((string) ($revisionRow['notes'] ?? ''));
    if ($note === '') {
        continue;
    }

    $recentChangeNotes[] = [
        'revision_label' => (string) ($revisionRow['revision_label'] ?? '-'),
        'notes' => $note,
        'created_at' => (string) ($revisionRow['created_at'] ?? ''),
    ];

    if (count($recentChangeNotes) >= 5) {
        break;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Dokumen Kebijakan Administrator | LCM</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=3.1">
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
    <a class="is-active" href="<?= e(route('admin.documents.php')) ?>">Dokumen Kebijakan</a>
    <a href="<?= e(route('admin.email-submissions.php')) ?>">Email SIMPER</a>
    <?php endif; ?>

    <a href="<?= e(route('admin.change-password.php')) ?>">Ubah Kata Sandi</a>
</nav>

<main id="main-content" class="container page-main">
    <?php if ($success): ?>
        <div class="alert alert-success" data-auto-dismiss="3500"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($errors->any()): ?>
        <div class="alert alert-error">
            <?= e((string) $errors->first()) ?>
        </div>
    <?php endif; ?>

    <section class="card card-elevated">
        <p class="section-eyebrow">Document Governance</p>
        <h1 class="panel-title">Manajemen Dokumen KTP-OHS-102</h1>
        <p class="panel-subtitle">Kelola versi dokumen Mine Permit & SIMPER agar selalu dapat dibaca publik dengan revisi terbaru.</p>
    </section>

    <section class="layout-split">
        <article class="card card-accent form-card">
            <h2 class="panel-title">Metadata Publik Dokumen</h2>
            <p class="small">Informasi ini tampil pada halaman publik dokumen. Gunakan judul dan deskripsi yang jelas agar mudah dipahami pembaca.</p>

            <form method="post" action="<?= e(route('admin.documents.meta.php')) ?>" class="stack">
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                <div class="form-group">
                    <label for="title">Judul Dokumen</label>
                    <input id="title" name="title" maxlength="190" value="<?= e((string) ($document['title'] ?? 'KTP-OHS-102 Mine Permit & SIMPER')) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi Dokumen</label>
                    <textarea id="description" name="description" rows="3" maxlength="5000"><?= e((string) ($document['description'] ?? '')) ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_public" value="1" <?= ((int) ($document['is_public'] ?? 0) === 1) ? 'checked' : '' ?>>
                        Publikasikan ke portal (dapat dibaca oleh publik)
                    </label>
                </div>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">Simpan Metadata</button>
                    <a class="btn btn-secondary" href="<?= e(route('portal.documents.show', ['code' => $documentCode])) ?>" target="_blank" rel="noopener noreferrer">Preview Halaman Publik</a>
                </div>
            </form>

            <hr>

            <h2 class="panel-title">Upload Revisi Baru (PDF)</h2>
            <p class="small">File lama tidak dihapus agar jejak revisi tetap terjaga. Ukuran maksimal 20 MB per file.</p>

            <form method="post" action="<?= e(route('admin.documents.revisions.store.php')) ?>" enctype="multipart/form-data" class="stack" data-document-upload-form>
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                <div class="form-group">
                    <label for="revision_label">Label Revisi</label>
                    <input id="revision_label" name="revision_label" maxlength="80" placeholder="Contoh: Rev7" required>
                </div>

                <div class="form-group">
                    <label for="revision_notes">Catatan Revisi</label>
                    <textarea id="revision_notes" name="revision_notes" rows="2" maxlength="5000" placeholder="Opsional"></textarea>
                </div>

                <div class="form-group">
                    <label for="document_file">File PDF</label>
                    <div class="doc-upload-zone" data-doc-dropzone>
                        <input id="document_file" name="document_file" type="file" accept="application/pdf,.pdf" required data-doc-file-input>
                        <p class="small">Tarik dan lepas file PDF di sini, atau klik untuk memilih file.</p>
                        <p class="small doc-selected-file" data-doc-selected-file>Belum ada file dipilih.</p>
                    </div>
                </div>

                <div class="upload-progress is-hidden" data-upload-progress>
                    <div class="upload-progress-track">
                        <span class="upload-progress-bar" data-upload-progress-bar></span>
                    </div>
                    <p class="small" data-upload-progress-label>Mengunggah revisi dokumen, mohon tunggu...</p>
                </div>

                <button class="btn btn-primary" type="submit">Upload Revisi</button>
            </form>
        </article>

        <article class="card table-card">
            <div class="toolbar">
                <div>
                    <h2 class="panel-title">Riwayat Revisi Dokumen</h2>
                    <p class="small">Revisi terbaru akan menjadi dokumen aktif yang ditampilkan pada publik.</p>
                </div>
                <p class="result-count"><?= e((string) count($revisions)) ?> revisi</p>
            </div>

            <?php if ($recentChangeNotes): ?>
                <section class="document-changelog" aria-label="Ringkasan perubahan terbaru">
                    <h3 class="panel-title">Ringkasan Perubahan Terbaru</h3>
                    <div class="document-changelog-list">
                        <?php foreach ($recentChangeNotes as $note): ?>
                            <article class="document-changelog-item">
                                <p class="document-changelog-meta">
                                    <span class="badge badge-window is-scheduled"><?= e((string) $note['revision_label']) ?></span>
                                    <span class="small"><?= e(document_format_datetime((string) $note['created_at'])) ?></span>
                                </p>
                                <p class="small"><?= e((string) $note['notes']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!$revisions): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">i</div>
                    <p>Belum ada revisi dokumen. Upload file PDF pertama untuk mempublikasikan dokumen.</p>
                </div>
            <?php else: ?>
                <section class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Revisi</th>
                            <th>Waktu Upload</th>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Checksum</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($revisions as $revision): ?>
                            <?php $isLatest = (int) ($revision['id'] ?? 0) === (int) ($document['revision_id'] ?? 0); ?>
                            <tr>
                                <td>
                                    <span class="badge badge-window <?= $isLatest ? 'is-live' : 'is-scheduled' ?>"><?= e((string) ($revision['revision_label'] ?? '-')) ?></span>
                                    <?php if ($isLatest): ?>
                                        <span class="small">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(document_format_datetime((string) ($revision['created_at'] ?? ''))) ?></td>
                                <td>
                                    <span class="small"><?= e((string) ($revision['original_filename'] ?? '-')) ?></span>
                                    <?php if (!empty($revision['notes'])): ?>
                                        <p class="small"><?= e((string) $revision['notes']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(document_format_size((int) ($revision['file_size'] ?? 0))) ?></td>
                                <td><span class="small"><?= e(substr((string) ($revision['checksum_sha256'] ?? '-'), 0, 16)) ?>...</span></td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="<?= e(route('portal.documents.file', ['code' => $documentCode, 'revision' => (int) ($revision['id'] ?? 0)])) ?>" target="_blank" rel="noopener noreferrer">Buka</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>
        </article>
    </section>
</main>

<script src="<?= e(asset('assets/app.js')) ?>?v=3.1"></script>
</body>
</html>
