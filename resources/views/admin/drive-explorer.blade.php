<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Drive Explorer | LCM</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=4.0">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.1">
</head>
<body class="admin-page">
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
            <span class="user-chip"><?= e((string) ($admin['email'] ?? 'admin')) ?></span>
            <a class="btn btn-ghost" href="<?= e(route('portal.index.php')) ?>">Portal Publik</a>
        </div>
    </div>
</header>

<?php if (($mode ?? '') !== 'pick'): ?>
<nav class="container quick-nav">
    <a href="<?= e(route('admin.dashboard.php')) ?>">Dashboard</a>
    <a href="<?= e(route('admin.submissions.index')) ?>">Monitoring Pengajuan</a>
    <a class="is-active" href="<?= e(route('admin.drive-explorer')) ?>">Google Drive Explorer</a>
    <a href="<?= e(route('admin.email-submissions.php')) ?>">Email SIMPER</a>
</nav>
<?php endif; ?>

<main class="container page-main">
    <?php if ($needsAuth ?? false): ?>
        <section class="card card-elevated" style="text-align: center; padding: 4rem 2rem; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <h1 class="panel-title" style="font-size: 1.75rem; margin-bottom: 1rem;">Hubungkan Google Drive</h1>
            <p class="panel-subtitle" style="max-width: 500px; margin: 0 auto 2.5rem; line-height: 1.6;">
                Untuk dapat memilih berkas langsung dari Google Drive Anda, silakan hubungkan akun Google Anda terlebih dahulu. 
                Sistem akan mengingat akun Anda untuk penggunaan berikutnya.
            </p>
            <a href="<?= e($authUrl) ?>" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1rem; border-radius: 12px; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"/></svg>
                Hubungkan Akun Google
            </a>
            <?php if (isset($error)): ?>
                <p style="color: #ef4444; margin-top: 1.5rem; font-size: 0.875rem;">Error: <?= e($error) ?></p>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="card card-elevated">
            <p class="section-eyebrow">Google Drive Integration</p>
            <h1 class="panel-title">Drive Explorer</h1>
            <p class="panel-subtitle">Telusuri folder Google Drive untuk memilih berkas yang akan dilampirkan pada pengajuan.</p>
        </section>

        <?php if (($mode ?? '') === 'pick'): ?>
            <div style="background: linear-gradient(135deg, #4338ca, #312e81); color: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                <span style="font-size: 1.5rem;">📁</span>
                <div>
                    <strong style="font-size: 1rem;">Mode Pilih Berkas</strong>
                    <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 2px;">Pilih berkas untuk slot: <strong><?= e(strtoupper($target ?? 'dokumen')) ?></strong>. Klik "Pilih" pada berkas yang sesuai.</div>
                </div>
                <button onclick="window.close()" class="btn btn-ghost" style="margin-left: auto; color: white; border-color: rgba(255,255,255,0.3);">✕ Batal</button>
            </div>
        <?php endif; ?>

        <section class="card table-card">
            <div class="toolbar">
                <div class="breadcrumb">
                    <?php
                    $baseExplorerRoute = route('admin.drive-explorer');
                    $modeParams = ($mode ?? '') === 'pick' ? '&mode=pick&target=' . e($target ?? '') : '';
                    ?>
                    <a href="<?= $baseExplorerRoute . '?folder_id=root' . $modeParams ?>" style="display: flex; align-items: center; gap: 6px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        My Drive
                    </a>
                    <?php if ($currentFolder): ?>
                        <span style="color: #cbd5e1;">/</span>
                        <span style="font-weight: 600; color: #1e293b;"><?= e($currentFolder->getName()) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="drive-list">
                <?php if (!$folders && !$files): ?>
                    <div class="empty-state p-md" style="text-align: center; padding: 3rem 0;">
                        <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.2;">📂</div>
                        <p style="color: #64748b; font-weight: 500;">Folder ini kosong.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($folders as $folder): ?>
                    <?php
                    $folderNavUrl = route('admin.drive-explorer', ['folder_id' => $folder->getId()]);
                    if (($mode ?? '') === 'pick') {
                        $folderNavUrl .= '&mode=pick&target=' . e($target ?? '');
                    }
                    ?>
                    <a href="<?= $folderNavUrl ?>" class="drive-item">
                        <div class="drive-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#fbbf24" class="drive-icon"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                        </div>
                        <span class="drive-name"><?= e($folder->getName()) ?></span>
                        <span class="drive-meta">Folder</span>
                    </a>
                <?php endforeach; ?>

                <?php foreach ($files as $file): ?>
                    <?php if ($file->getMimeType() === 'application/vnd.google-apps.folder') continue; ?>
                    <div class="drive-item">
                        <div class="drive-icon-wrapper">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#cbd5e1" class="drive-icon"><path d="M6 2c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/></svg>
                        </div>
                        <span class="drive-name"><?= e($file->getName()) ?></span>
                        <span class="drive-meta"><?= e($file->getMimeType()) ?></span>
                        <div class="drive-actions" style="display: flex; gap: 8px;">
                            <a href="<?= e($file->getWebViewLink()) ?>" target="_blank" class="btn btn-secondary btn-sm">Buka</a>
                            <?php if (($mode ?? '') === 'pick'): ?>
                                <button 
                                    type="button" 
                                    class="btn btn-primary btn-sm" 
                                    onclick="pickFile('<?= e($file->getWebViewLink()) ?>', '<?= e(addslashes($file->getName())) ?>')"
                                >
                                    Pilih
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </section>

        <section class="card card-quiet mt-md">
            <h2 class="panel-title">Informasi</h2>
            <p class="small">Berkas yang Anda pilih akan ditautkan ke pengajuan ini. Pastikan berkas tersebut memiliki izin akses yang tepat agar dapat dilihat oleh tim SHE/Admin.</p>
        </section>
    <?php endif; ?>
</main>

<script src="<?= e(asset('assets/app.js')) ?>?v=3.11"></script>
<?php if (($mode ?? '') === 'pick'): ?>
<script>
    function pickFile(url, name) {
        if (window.opener && !window.opener.closed) {
            window.opener.onFilePicked(url, name, '<?= e($target ?? '') ?>');
            window.close();
        } else {
            alert('Jendela utama telah tertutup. Gagal mengirim data.');
        }
    }
</script>
<?php endif; ?>
</body>
</html>
