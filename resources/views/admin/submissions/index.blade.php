<?php
$success = session('success');
$error = session('error');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pengajuan | LCM Safety Portal</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=3.5">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <style>
        .table-premium { border-radius: 20px; overflow: hidden; background: white; border: 1px solid var(--admin-border); }
        .table-premium th { background: #f8fafc; color: #475569; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 1.25rem 1rem; border-bottom: 2px solid #e2e8f0; }
        .table-premium td { padding: 1.25rem 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .table-premium tr:last-child td { border-bottom: none; }
        .table-premium tr:hover { background: #fcfcfc; }
        .search-bar { background: white; border-radius: 12px; border: 1px solid var(--admin-border); display: flex; align-items: center; padding: 0 1rem; gap: 8px; transition: all 0.2s; }
        .search-bar:focus-within { border-color: var(--admin-primary); box-shadow: 0 0 0 4px rgba(67, 56, 202, 0.1); }
        .search-bar input { border: none !important; background: transparent !important; flex: 1; padding: 12px 0 !important; font-size: 0.9rem; box-shadow: none !important; }
    </style>
</head>
<body class="admin-page">
<header class="topbar-admin">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <a class="brand" href="<?= e(route('admin.dashboard.php')) ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="<?= e(asset('assets/branding/remote/lcm-logo.png')) ?>" alt="Logo" width="40" height="40">
            <span style="color: white; font-weight: 700; font-size: 1.1rem; letter-spacing: -0.02em;">Safety Portal</span>
        </a>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span class="user-chip"><?= e(strtoupper($userRole)) ?></span>
            <form method="post" action="<?= e(route('admin.logout.php')) ?>">
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 8px;">Log Out</button>
            </form>
        </div>
    </div>
</header>

<main class="container page-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--admin-secondary); margin: 0; letter-spacing: -0.03em;">Monitoring Pengajuan</h1>
            <p style="color: var(--admin-text-muted); font-size: 0.95rem; margin-top: 4px;">Kelola alur kerja verifikasi SIMPER & Izin Kerja lainnya.</p>
        </div>
        <?php if (in_array($userRole, ['hrga', 'subcon', 'admin'])): ?>
            <a href="<?= e(route('admin.submissions.create')) ?>" class="btn btn-primary" style="padding: 14px 28px; border-radius: 14px; font-weight: 800; font-size: 1rem; box-shadow: 0 10px 15px -3px rgba(67, 56, 202, 0.2);">
                + Pengajuan Baru
            </a>
        <?php endif; ?>
    </div>

    <?php if ($success): ?>
        <div style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <i class="ti ti-check" style="font-size: 1.25rem;"></i> <?= e($success) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="card" style="padding: 1.5rem; border-radius: 20px;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Total Antrean</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--admin-secondary);"><?= $submissions->total() ?></div>
        </div>
        <div class="card" style="padding: 1.5rem; border-radius: 20px;">
            <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Role Aktif</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--admin-primary);"><?= e(strtoupper($userRole)) ?></div>
        </div>
        <div style="grid-column: span 2;">
            <form action="<?= e(route('admin.submissions.index')) ?>" method="get" class="search-bar">
                <i class="ti ti-search" style="color: #94a3b8; font-size: 1.1rem;"></i>
                <input type="text" name="search" value="<?= e(request('search')) ?>" placeholder="Cari Nama Pemohon...">
                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 6px 16px;">Cari</button>
            </form>
        </div>
    </div>

    <div class="table-premium">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 300px;">Pemohon</th>
                    <th>Kategori / Jenis</th>
                    <th>Perusahaan</th>
                    <th>Status Progres</th>
                    <th style="text-align: right;">Opsi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($submissions->isEmpty()): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 4rem; color: #94a3b8;">
                            <div style="font-size: 3rem; margin-bottom: 1rem; color: #cbd5e1;"><i class="ti ti-clipboard-list"></i></div>
                            <p style="font-weight: 600; margin: 0;">Belum ada data pengajuan yang ditemukan.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; background: #eef2ff; color: #4338ca; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                        <?= e(strtoupper(substr($sub->applicant_name, 0, 2))) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: var(--admin-secondary); font-size: 0.95rem;"><?= e($sub->applicant_name) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #475569; font-size: 0.85rem;"><?= e($sub->category->name) ?></div>
                                <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;"><?= e($sub->item_type) ?></div>
                            </td>
                            <td>
                                <?php if ($sub->submitted_by_vendor && $sub->vendor): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #10b981; font-size: 0.85rem;">
                                        <span style="display: inline-block; width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></span>
                                        <?= e($sub->vendor->company_name) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #059669; margin-top: 4px; font-weight: 500;">Vendor/Subcon</div>
                                <?php elseif ($sub->creator): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #4f46e5; font-size: 0.85rem;">
                                        <span style="display: inline-block; width: 6px; height: 6px; background: #4f46e5; border-radius: 50%;"></span>
                                        <?= e($sub->creator->full_name) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #3730a3; margin-top: 4px; font-weight: 500;">Admin/Staff</div>
                                <?php else: ?>
                                    <div style="font-weight: 600; color: #94a3b8; font-size: 0.85rem;">—</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= e($sub->status_color) ?>" style="padding: 6px 14px; font-size: 0.75rem; border-radius: 6px; font-weight: 800;">
                                    <?= e($sub->status_label) ?>
                                </span>
                                <?php if (in_array($userRole, ['admin', 'she']) && str_starts_with($sub->status, 'pending_')): ?>
                                    <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                                        &rsaquo; <?= e(str_replace('pending_', '', $sub->status)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display:inline-flex; align-items:center; gap:0.5rem;">
                                    <a href="<?= e(route('admin.submissions.show', $sub->id)) ?>" class="btn btn-secondary" style="padding: 8px 14px; font-size: 0.85rem; border-radius: 10px; font-weight: 800; border: 2px solid #e2e8f0; background: white !important; color: #475569 !important;">
                                        Detail &rsaquo;
                                    </a>
                                    <?php if (in_array($userRole, ['admin', 'she'])): ?>
                                        <form class="form-destroy-index" action="<?= e(route('admin.submissions.destroy', $sub->id)) ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn" style="padding: 8px 12px; font-size: 0.8rem; border-radius: 10px; font-weight: 800; border: 2px solid #fecaca; background: #fef2f2; color: #dc2626; cursor:pointer;" onclick="showSimpleConfirm('Hapus pengajuan ini secara permanen?', this.closest('form'))">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($submissions->hasPages()): ?>
        <div style="margin-top: 2rem;">
            <?= $submissions->links() ?>
        </div>
    <?php endif; ?>

    <script>
        function showSimpleConfirm(message, form) {
            const confirmed = confirm(message);
            if (confirmed && form) {
                form.submit();
            }
        }
    </script>
</main>
</body>
</html>
