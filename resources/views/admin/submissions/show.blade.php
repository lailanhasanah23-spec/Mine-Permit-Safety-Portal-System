<?php
$admin = App\Support\Legacy\LegacyAuth::user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan | LCM Safety Portal</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=3.5">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== BASE ===== */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f0f4f8;
            color: #1e293b;
        }

        /* ===== LAYOUT ===== */
        .sp-shell {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* ===== PAGE HEADER ===== */
        .sp-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem 2rem;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(0,0,0,0.04);
        }

        .sp-page-header-left h1 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 0.5rem;
            letter-spacing: -0.02em;
        }

        .sp-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .sp-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            letter-spacing: 0.03em;
        }

        .sp-badge--blue   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .sp-badge--green  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .sp-badge--amber  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .sp-badge--red    { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .sp-badge--purple { background: #faf5ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .sp-badge--slate  { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

        /* ===== PROGRESS TIMELINE ===== */
        .sp-timeline-wrapper {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 1.75rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .sp-timeline-title {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }

        .sp-timeline {
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .sp-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 1;
            min-width: 80px;
        }

        .sp-step-connector {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            min-width: 24px;
            margin-top: -28px;
            position: relative;
            z-index: 0;
        }

        .sp-step-connector.done { background: #22c55e; }
        .sp-step-connector.active { background: linear-gradient(90deg, #22c55e, #e2e8f0); }

        .sp-step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 800;
            position: relative;
            z-index: 1;
            transition: all 0.2s;
            border: 2.5px solid #e2e8f0;
            background: #f8fafc;
            color: #94a3b8;
        }

        .sp-step.is-done .sp-step-circle {
            background: #22c55e;
            border-color: #22c55e;
            color: #fff;
        }

        .sp-step.is-active .sp-step-circle {
            background: #fff;
            border-color: #3b82f6;
            color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .sp-step-label {
            margin-top: 0.6rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: center;
        }

        .sp-step.is-done .sp-step-label  { color: #16a34a; }
        .sp-step.is-active .sp-step-label { color: #2563eb; }

        .sp-step-pulse {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 50%;
            background: rgba(59,130,246,0.2);
            animation: pulse-ring 1.5s ease-out infinite;
        }

        @keyframes pulse-ring {
            0%   { transform: scale(0.95); opacity: 1; }
            70%  { transform: scale(1.25); opacity: 0; }
            100% { transform: scale(1.25); opacity: 0; }
        }

        /* ===== GRID LAYOUT ===== */
        .sp-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) 360px;
            gap: 1.5rem;
            align-items: start;
        }

        .sp-main, .sp-aside {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* ===== CARD ===== */
        .sp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.03);
            transition: box-shadow 0.2s;
        }

        .sp-card:hover {
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 28px rgba(0,0,0,0.07);
        }

        .sp-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.125rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .sp-card-header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sp-card-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .sp-card-icon--blue   { background: #eff6ff; color: #2563eb; }
        .sp-card-icon--orange { background: #fff7ed; color: #ea580c; }
        .sp-card-icon--green  { background: #f0fdf4; color: #16a34a; }

        .sp-card-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .sp-card-body {
            padding: 1.5rem;
        }

        /* ===== FILE ITEMS ===== */
        .sp-file-stack { display: flex; flex-direction: column; gap: 0.75rem; }

        .sp-file-item {
            display: grid;
            grid-template-columns: 1fr auto;
            grid-template-rows: auto auto;
            gap: 0 1rem;
            padding: 1rem 1.125rem;
            background: #f8fafc;
            border: 1.5px solid #e8eef6;
            border-radius: 14px;
            transition: border-color 0.15s, background 0.15s;
        }

        .sp-file-item:hover { border-color: #c7d7f0; background: #f0f6ff; }
        .sp-file-item.is-empty { border-style: dashed; border-color: #dde5ef; }
        .sp-file-item.is-empty:hover { border-color: #b0c4de; }

        .sp-file-info { grid-column: 1; grid-row: 1; }
        .sp-file-actions { grid-column: 2; grid-row: 1; align-self: center; }
        .sp-file-upload { grid-column: 1 / -1; grid-row: 2; margin-top: 0.75rem; }

        .sp-file-label {
            font-size: 0.68rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.3rem;
        }

        .sp-file-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            line-height: 1.4;
        }

        .sp-file-name {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .sp-file-name.is-empty {
            color: #b8c4d4;
            font-weight: 500;
            font-style: italic;
        }

        .sp-file-actions {
            display: flex;
            gap: 0.4rem;
            align-items: center;
            flex-shrink: 0;
        }

        .sp-file-actions a, .sp-file-actions button {
            touch-action: manipulation;
        }

        /* ===== UPLOAD STRIP ===== */
        .sp-upload-strip {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            padding: 0.75rem 1rem;
            background: #f1f5f9;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .sp-upload-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .sp-upload-strip input[type="file"] {
            font-size: 0.78rem;
            color: #475569;
            max-width: 240px;
        }

        /* ===== BUTTONS ===== */
        .sp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            white-space: nowrap;
            text-decoration: none;
        }

        .sp-btn--view {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .sp-btn--view:hover { background: #dbeafe; }

        .sp-btn--drive {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .sp-btn--drive:hover { background: #dcfce7; }

        .sp-btn--delete {
            background: transparent;
            color: #ef4444;
            border: 1px solid #fecaca;
            padding: 0.4rem;
        }
        .sp-btn--delete:hover { background: #fef2f2; }

        .sp-btn--primary {
            background: #2563eb;
            color: #fff;
            border: 1px solid #1d4ed8;
            padding: 0.7rem 1.25rem;
            width: 100%;
            font-size: 0.875rem;
        }
        .sp-btn--primary:hover { background: #1d4ed8; }

        .sp-btn--success {
            background: #16a34a;
            color: #fff;
            border: 1px solid #15803d;
            padding: 0.75rem 1.25rem;
            width: 100%;
            font-size: 0.875rem;
        }
        .sp-btn--success:hover { background: #15803d; }

        .sp-btn--danger {
            background: #dc2626;
            color: #fff;
            border: 1px solid #b91c1c;
            padding: 0.75rem 1.25rem;
            width: 100%;
            font-size: 0.875rem;
        }
        .sp-btn--danger:hover { background: #b91c1c; }

        .sp-btn--secondary {
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 1.25rem;
            width: 100%;
            font-size: 0.875rem;
        }
        .sp-btn--secondary:hover { background: #f1f5f9; }

        /* ===== ASIDE CARDS ===== */
        .sp-info-row {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .sp-info-row:last-child { border-bottom: none; }

        .sp-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .sp-info-key {
            font-size: 0.68rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.2rem;
        }

        .sp-info-val {
            font-size: 0.875rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* ===== PARAMEDIC CARD ===== */
        .sp-note-card {
            border-left: 4px solid #10b981 !important;
        }

        .sp-note-display {
            background: #f0fdf4;
            border: 1px solid #d1fae5;
            border-radius: 12px;
            padding: 1rem 1.125rem;
            font-size: 0.875rem;
            line-height: 1.65;
            color: #065f46;
            margin-bottom: 1rem;
        }

        .sp-textarea {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-family: inherit;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #1e293b;
            resize: vertical;
            outline: none;
            transition: border-color 0.15s;
            margin-bottom: 0.75rem;
        }
        .sp-textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }

        /* ===== SHE CARD ===== */
        .sp-she-card {
            background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%) !important;
            border: 1.5px solid #bfdbfe !important;
        }

        .sp-she-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0.75rem 0;
            font-size: 0.7rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .sp-she-divider::before,
        .sp-she-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dbeafe;
        }

        /* ===== SECTION LABEL ===== */
        .sp-section-label {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        /* ===== UPLOAD FEEDBACK ===== */
        .sp-upload-progress {
            display: none;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            color: #2563eb;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .sp-upload-progress.visible { display: flex; }
        .sp-spinner {
            width: 14px; height: 14px;
            border: 2px solid #bfdbfe;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .sp-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 680px) {
            .sp-shell { padding: 1rem; }
            .sp-page-header { padding: 1.125rem 1.25rem; flex-direction: column; align-items: flex-start; }
            .sp-card-header, .sp-card-body { padding: 1rem 1.125rem; }
            .sp-file-item { grid-template-columns: 1fr; }
            .sp-file-actions { margin-top: 0.5rem; }
            .sp-timeline-wrapper { padding: 1.25rem; }
            .sp-btn { padding: 0.6rem 0.9rem; font-size: 0.85rem; }
        }

        /* Lightweight thumbnail preview */
        .sp-file-preview { margin-top: 0.6rem; }
        .sp-file-preview img.sp-thumb { max-width: 140px; max-height: 90px; border-radius: 8px; display:block; object-fit:cover; background:#e6eef8; }
    </style>
</head>
<body>
<?php
// ---- STEP LOGIC ----
if ($submission->category->code === 'SIMPER_PERMIT') {
    if ($submission->item_type === 'SIMPER') {
        $steps = [
            'pending_hrga'      => 'HRGA',
            'pending_paramedic' => 'MEDIS',
            'pending_tod'       => 'TOD',
            'pending_she'       => 'SHE',
            'approved'          => 'Selesai',
        ];
    } else {
        $steps = [
            'pending_hrga'      => 'HRGA',
            'pending_paramedic' => 'MEDIS',
            'pending_she'       => 'SHE',
            'approved'          => 'Selesai',
        ];
    }
} else {
    $steps = [
        'pending_hrga' => 'HRGA',
        'pending_she'  => 'SHE',
        'approved'     => 'Selesai',
    ];
}

$flowLabel = $submission->item_type === 'SIMPER' ? 'SIMPER' : 'Permit';
$stepKeys  = array_keys($steps);
$stepCount = count($stepKeys);
$curStatus = $submission->status;
$curIdx    = array_search($curStatus, $stepKeys);
if ($curIdx === false) {
    $curIdx = ($curStatus === 'rejected') ? $stepCount - 2 : 0;
}
?>

<main class="sp-shell">

    <!-- ===== PAGE HEADER ===== -->
    <div class="sp-page-header">
        <div class="sp-page-header-left">
            <h1><?= e($submission->applicant_name ?? 'Detail Pengajuan') ?></h1>
            <div class="sp-meta-row">
                <span class="sp-badge sp-badge--slate"><?= e($submission->submission_code ?? $submission->id) ?></span>
                <span class="sp-badge <?= $submission->item_type === 'SIMPER' ? 'sp-badge--blue' : 'sp-badge--purple' ?>">
                    <?= e($submission->item_type) ?>
                </span>
                <?php
                $statusMap = [
                    'pending_hrga'      => ['label' => 'Menunggu HRGA',   'cls' => 'sp-badge--amber'],
                    'pending_paramedic' => ['label' => 'Menunggu MEDIS',  'cls' => 'sp-badge--amber'],
                    'pending_tod'       => ['label' => 'Menunggu TOD',    'cls' => 'sp-badge--amber'],
                    'pending_she'       => ['label' => 'Menunggu SHE',    'cls' => 'sp-badge--blue'],
                    'approved'          => ['label' => 'Disetujui',       'cls' => 'sp-badge--green'],
                    'rejected'          => ['label' => 'Ditolak',         'cls' => 'sp-badge--red'],
                ];
                $si = $statusMap[$curStatus] ?? ['label' => ucfirst($curStatus), 'cls' => 'sp-badge--slate'];
                ?>
                <span class="sp-badge <?= $si['cls'] ?>"><?= $si['label'] ?></span>
                <?php if ($submission->created_at): ?>
                    <span class="sp-badge sp-badge--slate">
                        <i class="ti ti-calendar" style="font-size:0.8rem;"></i>
                        <?= e($submission->created_at->format('d M Y')) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:0.55rem;">
            <?php if (in_array($userRole, ['admin', 'she'])): ?>
                <?php if (! empty($submission->gdrive_folder_id)): ?>
                    <a href="<?= e(route('admin.drive-explorer', ['folder_id' => $submission->gdrive_folder_id])) ?>" class="sp-btn sp-btn--secondary" style="width:auto; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;">
                        <i class="ti ti-folder"></i> Buka Folder Drive
                    </a>
                <?php endif; ?>
                <form action="<?= e(route('admin.submissions.sync-drive-folder', $submission->id)) ?>" method="post" style="display:inline;">
                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                    <button type="submit" class="sp-btn" style="width:auto; background:#ecfeff; color:#0f766e; border:1px solid #99f6e4; padding:0.65rem 0.95rem;">
                        <i class="ti ti-cloud-upload"></i> Sync Drive Folder
                    </button>
                </form>
            <?php endif; ?>
            <?php if (in_array($userRole, ['admin', 'she'])): ?>
                <form class="form-destroy" action="<?= e(route('admin.submissions.destroy', $submission->id)) ?>" method="post" style="display:inline;">
                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" class="sp-btn" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; width:auto; padding:0.65rem 0.95rem;" onclick="showConfirmModal('destroy')">
                        <i class="ti ti-trash"></i> Hapus Pengajuan
                    </button>
                </form>
            <?php endif; ?>
            <a href="javascript:history.back()" class="sp-btn sp-btn--secondary" style="width:auto;">
                <i class="ti ti-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- ===== PROGRESS TIMELINE ===== -->
    <!-- ===== SUBMITTER AUDIT INFO ===== -->
    <div class="sp-card" style="margin-bottom: 1.5rem;">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="sp-card-icon sp-card-icon--blue" style="background: #f0fdf4; color: #16a34a;">
                    <i class="ti ti-info-circle"></i>
                </div>
                <h3 class="sp-card-title">Informasi Pengajuan</h3>
            </div>
        </div>
        <div class="sp-card-body" style="padding: 1.5rem; display: flex; gap: 2rem; flex-wrap: wrap;">
            <!-- Submitter Info -->
            <div style="flex: 1; min-width: 250px;">
                <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Pengaju</div>
                <?php if ($submission->submitted_by_vendor && $submission->vendor): ?>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
                        <span style="font-weight: 700; color: #10b981; font-size: 0.95rem;"><?= e($submission->vendor->company_name) ?></span>
                    </div>
                    <div style="font-size: 0.75rem; color: #059669; font-weight: 500;">Vendor / Subkontraktor</div>
                <?php elseif ($submission->creator): ?>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <span style="display: inline-block; width: 8px; height: 8px; background: #4f46e5; border-radius: 50%;"></span>
                        <span style="font-weight: 700; color: #4f46e5; font-size: 0.95rem;"><?= e($submission->creator->full_name) ?></span>
                    </div>
                    <div style="font-size: 0.75rem; color: #3730a3; font-weight: 500;">Admin / Staff (<?= e(strtoupper($userRole)) ?>)</div>
                <?php else: ?>
                    <div style="font-weight: 600; color: #94a3b8; font-size: 0.85rem;">—</div>
                <?php endif; ?>
            </div>
            <!-- Submission Date -->
            <div style="flex: 1; min-width: 250px;">
                <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Tanggal Pengajuan</div>
                <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                    <?php if ($submission->created_at): ?>
                        <?= e($submission->created_at->format('d F Y H:i')) ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </div>
            </div>

            <?php if (! empty($submission->gdrive_folder_id)): ?>
                <div style="flex: 1; min-width: 250px;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Folder Drive</div>
                    <div style="font-weight: 700; color: #16a34a; font-size: 0.95rem; margin-bottom: 0.35rem;">Tersinkron</div>
                    <a href="<?= e(route('admin.drive-explorer', ['folder_id' => $submission->gdrive_folder_id])) ?>" style="font-size: 0.82rem; color: #2563eb; font-weight: 600; text-decoration: none; word-break: break-all;">
                        Buka folder pengajuan
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== PROGRESS TIMELINE ===== -->
    <div class="sp-timeline-wrapper">
        <div class="sp-timeline-title">Status Pengajuan <?= e($flowLabel) ?></div>
        <div class="sp-timeline">
            <?php foreach ($stepKeys as $i => $key): ?>
                <?php
                $isDone   = ($i < $curIdx) || ($curStatus === 'approved');
                $isActive = ($i === $curIdx) && $curStatus !== 'approved';
                $cls = $isDone ? 'is-done' : ($isActive ? 'is-active' : '');
                ?>
                <div class="sp-step <?= $cls ?>">
                    <div class="sp-step-circle" style="position:relative;">
                        <?php if ($isActive): ?><div class="sp-step-pulse"></div><?php endif; ?>
                        <?= $isDone ? '<i class="ti ti-check" style="font-size:1rem;"></i>' : ($i + 1) ?>
                    </div>
                    <div class="sp-step-label"><?= e($steps[$key]) ?></div>
                </div>
                <?php if ($i < $stepCount - 1):
                    $connDone   = ($i < $curIdx - 1) || ($curStatus === 'approved' && $i < $stepCount - 1);
                    $connActive = ($i === $curIdx - 1) && $curStatus !== 'approved';
                    $connCls    = $connDone ? 'done' : ($connActive ? 'active' : '');
                ?>
                    <div class="sp-step-connector <?= $connCls ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===== MAIN GRID ===== -->
    <div class="sp-layout">

        <!-- LEFT COLUMN -->
        <div class="sp-main">

            <!-- PANEL 1: Identitas -->
            <div class="sp-card">
                <div class="sp-card-header">
                    <div class="sp-card-header-left">
                        <div class="sp-card-icon sp-card-icon--blue"><i class="ti ti-id-badge-2"></i></div>
                        <h3 class="sp-card-title">Berkas Identitas Diri</h3>
                    </div>
                    <span class="sp-badge sp-badge--blue">HRGA</span>
                </div>
                <div class="sp-card-body">
                    <div class="sp-file-stack">
                        <?php
                        $hrgaFiles = $submission->files()->whereIn('uploader_role', ['hrga', 'subcon'])->get()->keyBy('file_type');
                        $idTypes = [
                            'ktp'       => 'Kartu Tanda Penduduk (KTP)',
                            'mcu'       => 'Hasil Medical Check-Up (MCU)',
                            'sim'       => 'SIM A/B/C / Izin Operasi',
                            'foto_diri' => 'Pas Foto (Background Merah/Biru)',
                        ];
                        ?>
                        <?php foreach ($idTypes as $type => $label): ?>
                            
                            <?php $f = $hrgaFiles[$type] ?? null; ?>

                            <div class="sp-file-item <?= $f ? '' : 'is-empty' ?>">
                                <div class="sp-file-info">
                                    <div class="sp-file-label"><?= e($label) ?></div>
                                    <div class="sp-file-name <?= $f ? '' : 'is-empty' ?>">
                                        <?= $f ? e($f->file_name) : 'Belum dilampirkan' ?>
                                    </div>
                                        <?php if ($f):
                                            $ext = strtolower(pathinfo($f->file_name, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','tif','tiff']);
                                        ?>
                                            <?php if ($isImage): ?>
                                                <div class="sp-file-preview">
                                                    <img class="sp-thumb" data-src="<?= e(route('admin.submissions.download', [$submission->id, $f->id])) ?>" alt="<?= e($f->file_name) ?>" loading="lazy" />
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                </div>

                                <div class="sp-file-actions">
                                    <?php if ($f && ($userRole !== 'paramedic' || $type === 'mcu' || in_array($userRole, ['admin', 'she']))): ?>
                                        <a href="<?= e(route('admin.submissions.download', [$submission->id, $f->id])) ?>" target="_blank" rel="noopener noreferrer" class="sp-btn sp-btn--view" aria-label="Lihat <?= e($f->file_name) ?>">
                                            <i class="ti ti-eye"></i> Lihat
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($f && (in_array($userRole, ['admin', 'she']) || $f->uploader_role === $userRole || ($userRole === 'subcon' && $f->uploader_role === 'hrga'))): ?>
                                            <form class="form-delete-hrga" action="<?= e(route('admin.submissions.delete-file', [$submission->id, $f->id])) ?>" method="POST" style="display:inline;" aria-label="Hapus <?= e($f->file_name) ?>">
                                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="sp-btn sp-btn--delete" title="Hapus" onclick="showConfirmModal('delete-file-hrga', this)">
                                                <i class="ti ti-trash" style="font-size:0.9rem;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (in_array($userRole, ['hrga', 'subcon', 'admin']) && in_array($submission->status, ['pending_hrga', 'pending_paramedic', 'pending_tod', 'pending_she', 'rejected'])): ?>
                                        <button type="button" class="sp-btn sp-btn--drive" onclick="browseDrive('<?= e($type) ?>')" title="Pilih dari Google Drive" aria-label="Pilih dari Google Drive untuk <?= e($label) ?>">
                                            <i class="ti ti-brand-google-drive" style="font-size:0.9rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <?php 
                                // Allow HRGA/Subcon to edit when pending_hrga or rejected; Admin/SHE can always edit
                                $canEditHrga = (in_array($userRole, ['hrga', 'subcon']) && in_array($submission->status, ['pending_hrga', 'pending_paramedic', 'pending_tod', 'pending_she', 'rejected'])) || in_array($userRole, ['admin', 'she']);
                                if ($canEditHrga): 
                                ?>
                                    <div class="sp-file-upload">
                                        <form class="ajax-upload-form sp-upload-strip" action="<?= e(route('admin.submissions.upload-hrga', $submission->id)) ?>" method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="type" value="<?= e($type) ?>">
                                            <span class="sp-upload-label"><?= $f ? 'Ganti file' : 'Upload file' ?></span>
                                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.bmp,.tif,.tiff,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv" required>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- HRGA/Subcon: Forward/Status Button -->
                    <?php if (in_array($userRole, ['hrga', 'subcon', 'admin'])): ?>
                        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                            <?php if ($submission->status === 'pending_hrga'): ?>
                                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; padding:0.875rem 1rem; background:#fffbeb; border:1px solid #fde68a; border-radius:12px;">
                                    <i class="ti ti-info-circle" style="color:#d97706; font-size:1.1rem; flex-shrink:0;"></i>
                                    <span style="font-size:0.82rem; color:#92400e; font-weight:600; line-height:1.5;">Pastikan semua berkas identitas sudah diunggah sebelum melanjutkan ke tahap berikutnya.</span>
                                </div>
                                <form action="<?= e(route('admin.submissions.forward-hrga', $submission->id)) ?>" method="post" class="forward-form-hrga">
                                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                    <button type="button" class="sp-btn sp-btn--primary" onclick="showConfirmModal('forward-hrga')" style="font-size:0.9rem; padding: 0.875rem;">
                                        <i class="ti ti-send"></i> Ajukan Pengajuan
                                    </button>
                                </form>
                            <?php elseif ($submission->status === 'rejected'): ?>
                                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; padding:0.875rem 1rem; background:#fee2e2; border:1px solid #fecaca; border-radius:12px;">
                                    <i class="ti ti-alert-triangle" style="color:#dc2626; font-size:1.1rem; flex-shrink:0;"></i>
                                    <div>
                                        <div style="font-size:0.82rem; color:#991b1b; font-weight:600;">Pengajuan Ditolak</div>
                                        <div style="font-size:0.75rem; color:#991b1b; margin-top:0.25rem;"><?= e($submission->she_notes ?: '') ?></div>
                                    </div>
                                </div>
                                <form action="<?= e(route('admin.submissions.forward-hrga', $submission->id)) ?>" method="post" class="forward-form-hrga">
                                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="from_rejected" value="1">
                                    <button type="button" class="sp-btn sp-btn--warning" onclick="showConfirmModal('forward-hrga')" style="font-size:0.9rem; padding: 0.875rem;">
                                        <i class="ti ti-arrow-back"></i> Ajukan Pengajuan Ulang
                                    </button>
                                </form>
                            <?php else: ?>
                                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; padding:0.875rem 1rem; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;">
                                    <i class="ti ti-circle-check" style="color:#16a34a; font-size:1.1rem; flex-shrink:0;"></i>
                                    <span style="font-size:0.82rem; color:#166534; font-weight:600; line-height:1.5;">Pengajuan ini telah dikirim ke tahap berikutnya (<?= e($submission->status_label) ?>).</span>
                                </div>
                                <button type="button" class="sp-btn" disabled style="background:#f1f5f9; color:#94a3b8; border:1px solid #e2e8f0; font-size:0.9rem; padding: 0.875rem; cursor:not-allowed; opacity:0.8;">
                                    <i class="ti ti-circle-check"></i> Sudah Diajukan
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PANEL 2: TOD (SIMPER only, hidden from Paramedic) -->
            <?php if ($submission->item_type === 'SIMPER' && $userRole !== 'paramedic'): ?>
                <div class="sp-card">
                    <div class="sp-card-header">
                        <div class="sp-card-header-left">
                            <div class="sp-card-icon sp-card-icon--orange"><i class="ti ti-tool"></i></div>
                            <h3 class="sp-card-title">Verifikasi Teknis (TOD)</h3>
                        </div>
                        <span class="sp-badge sp-badge--amber">TOD</span>
                    </div>
                    <div class="sp-card-body">
                        <div class="sp-file-stack">
                            <?php
                            $todFiles = $submission->files()->where('uploader_role', 'tod')->get();
                            ?>
                            
                            <div class="sp-file-item <?= $todFiles->isEmpty() ? 'is-empty' : '' ?>">
                                <div class="sp-file-info">
                                    <div class="sp-file-label">Hasil Verifikasi Teknis (TOD)</div>
                                    <div class="sp-file-name <?= $todFiles->isEmpty() ? 'is-empty' : '' ?>">
                                        <?= $todFiles->isEmpty() ? 'Menunggu Verifikasi (Teori & Praktek)' : count($todFiles) . ' Berkas Terunggah' ?>
                                    </div>
                                    
                                    <?php if (!$todFiles->isEmpty()): ?>
                                        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                            <?php foreach ($todFiles as $tf): ?>
                                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                        <i class="ti ti-file-description" style="color: #64748b; font-size: 1.1rem;"></i>
                                                        <span style="font-size: 0.85rem; font-weight: 500; color: #1e293b;"><?= e($tf->file_name) ?></span>
                                                    </div>
                                                    <div style="display: flex; gap: 0.5rem;">
                                                        <a href="<?= e(route('admin.submissions.download', [$submission->id, $tf->id])) ?>" target="_blank" class="sp-btn sp-btn--view" style="padding: 0.4rem 0.6rem; font-size: 0.75rem;">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                        <?php if (in_array($userRole, ['admin', 'she', 'tod'])): ?>
                                                            <form action="<?= e(route('admin.submissions.delete-file', [$submission->id, $tf->id])) ?>" method="POST" style="display:inline;">
                                                                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                                                <input type="hidden" name="_method" value="DELETE">
                                                                <button type="button" class="sp-btn sp-btn--delete" style="padding: 0.4rem 0.6rem; font-size: 0.75rem;" onclick="showConfirmModal('delete-file-tod', this)">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="sp-file-actions">
                                    <?php if (in_array($userRole, ['tod', 'admin']) && $submission->status !== 'approved'): ?>
                                        <button type="button" class="sp-btn sp-btn--drive" onclick="browseDrive('hasil_verifikasi_tod')" title="Pilih dari Google Drive">
                                            <i class="ti ti-brand-google-drive" style="font-size:0.9rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <?php 
                                $canEditTod = (in_array($userRole, ['tod']) && in_array($submission->status, ['pending_tod', 'pending_she', 'rejected'])) || in_array($userRole, ['admin']);
                                if ($canEditTod): 
                                ?>
                                    <div class="sp-file-upload">
                                        <form class="ajax-upload-form sp-upload-strip" action="<?= e(route('admin.submissions.upload-tod', $submission->id)) ?>" method="post" enctype="multipart/form-data">
                                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                            <span class="sp-upload-label"><?= !$todFiles->isEmpty() ? 'Tambah berkas' : 'Upload berkas (Multi)' ?></span>
                                            <input type="file" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.bmp,.tif,.tiff,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv" required>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- TOD: Tombol Lanjutkan -->
                        <?php if (in_array($userRole, ['tod', 'admin']) && $submission->status === 'pending_tod'): ?>
                            <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; padding:0.875rem 1rem; background:#fff7ed; border:1px solid #fed7aa; border-radius:12px;">
                                    <i class="ti ti-info-circle" style="color:#ea580c; font-size:1.1rem; flex-shrink:0;"></i>
                                    <span style="font-size:0.82rem; color:#9a3412; font-weight:600; line-height:1.5;">Pastikan hasil tes verifikasi (Teori & Praktek) sudah diunggah sebelum melanjutkan.</span>
                                </div>
                                <form action="<?= e(route('admin.submissions.forward-tod', $submission->id)) ?>" method="post" class="forward-form-tod">
                                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                    <button type="button" class="sp-btn sp-btn--primary" onclick="showConfirmModal('forward-tod')" style="background:#ea580c; border-color:#c2410c; font-size:0.9rem; padding:0.875rem;">
                                        <i class="ti ti-send"></i> Ajukan Hasil Verifikasi TOD ke SHE
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Paramedic Note (placed below TOD) -->
            <?php if (in_array($userRole, ['paramedic', 'admin', 'hrga', 'subcon', 'she'])): ?>
                <div class="sp-card sp-note-card">
                    <div class="sp-card-header">
                        <div class="sp-card-header-left">
                            <div class="sp-card-icon sp-card-icon--green"><i class="ti ti-stethoscope"></i></div>
                            <h3 class="sp-card-title">Catatan Paramedic</h3>
                        </div>
                        <span class="sp-badge sp-badge--green">MEDIS</span>
                    </div>
                    <div class="sp-card-body">
                        <?php if ($submission->paramedic_notes): ?>
                            <div class="sp-note-display">
                                <?= nl2br(e($submission->paramedic_notes)) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (in_array($userRole, ['paramedic', 'admin'])): ?>
                            <form class="form-paramedic-verify" action="<?= e($submission->status === 'pending_paramedic' ? route('admin.submissions.paramedic-verify', $submission->id) : route('admin.submissions.paramedic-feedback', $submission->id)) ?>" method="post">
                                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                <textarea name="paramedic_notes" class="sp-textarea" rows="3" placeholder="Tulis feedback paramedic..."><?= e($submission->paramedic_notes ?? '') ?></textarea>
                                <?php if ($submission->status === 'pending_paramedic'): ?>
                                    <button type="button" class="sp-btn sp-btn--success" onclick="showConfirmModal('paramedic-verify', this)">
                                        <i class="ti ti-check"></i> Ajukan Hasil Verifikasi Medis
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="sp-btn sp-btn--secondary">
                                        <i class="ti ti-device-floppy"></i> Simpan Catatan
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php elseif (!$submission->paramedic_notes): ?>
                            <p style="font-size:0.82rem; color:#94a3b8; text-align:center; margin:0; padding: 0.5rem 0;">Belum ada catatan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /sp-main -->

        <!-- RIGHT COLUMN -->
        <div class="sp-aside">

            <!-- Applicant Info -->
            <div class="sp-card">
                <div class="sp-card-header">
                    <div class="sp-card-header-left">
                        <div class="sp-card-icon sp-card-icon--blue"><i class="ti ti-user-circle"></i></div>
                        <h3 class="sp-card-title">Informasi Pemohon</h3>
                    </div>
                    <?php if (in_array($userRole, ['hrga', 'subcon'])): ?>
                        <button type="button" class="sp-btn sp-btn--secondary" onclick="showEditModal()" style="padding: 0.45rem 0.7rem; font-size: 0.75rem; width:auto; flex-shrink:0;">
                            <i class="ti ti-edit"></i> Edit Data
                        </button>
                    <?php endif; ?>
                </div>
                <div class="sp-card-body" style="padding-top: 1rem; padding-bottom: 1rem;">
                    <div class="sp-info-row">
                        <div class="sp-info-icon" style="background:#eff6ff; color:#2563eb;"><i class="ti ti-user"></i></div>
                        <div>
                            <div class="sp-info-key">Nama Pemohon</div>
                            <div class="sp-info-val"><?= e($submission->applicant_name ?: '—') ?></div>
                        </div>
                    </div>
                    <!-- NIK and company/department removed from display as requested -->
                    <?php if ($submission->item_identifier): ?>
                        <div class="sp-info-row">
                            <div class="sp-info-icon" style="background:#fdf2f8; color:#db2777;"><i class="ti ti-tag"></i></div>
                            <div>
                                <div class="sp-info-key">Unit / Lambung / Lokasi</div>
                                <div class="sp-info-val" style="color:#db2777;"><?= e($submission->item_identifier) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Edit Applicant Modal -->
            <div id="editApplicantModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9998;align-items:center;justify-content:center;">
                <div style="background:white;border-radius:12px;padding:2rem;max-width:500px;width:90%;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);max-height:90vh;overflow-y:auto;">
                    <h3 style="margin:0 0 1.5rem 0;font-size:1.25rem;font-weight:600;color:#1f2937;">
                        <i class="ti ti-edit" style="margin-right:0.5rem;"></i> Edit Informasi Pemohon
                    </h3>
                    <form action="<?= e(route('admin.submissions.update', $submission->id)) ?>" method="post">
                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                        <div style="margin-bottom:1rem;">
                            <label style="display:block;margin-bottom:0.5rem;font-weight:600;color:#374151;font-size:0.9rem;">Nama Pemohon</label>
                            <input type="text" name="applicant_name" value="<?= e($submission->applicant_name ?? '') ?>" class="form-control" style="width:100%;padding:0.75rem;border:1px solid #d1d5db;border-radius:8px;font-size:0.95rem;">
                        </div>
                        <!-- NIK, company and department fields removed from edit modal -->
                        <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                            <button type="button" onclick="closeEditModal()" style="background:#f3f4f6;color:#374151;padding:0.75rem 1.25rem;border:none;border-radius:8px;cursor:pointer;font-weight:500;">
                                Batal
                            </button>
                            <button type="submit" style="background:#3b82f6;color:white;padding:0.75rem 1.25rem;border:none;border-radius:8px;cursor:pointer;font-weight:500;">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SHE Decision -->
            <?php if (in_array($userRole, ['she', 'admin']) && in_array($submission->status, ['pending_she', 'rejected'])): ?>
                <div class="sp-card sp-she-card">
                    <div class="sp-card-header" style="background:transparent; border-bottom-color:#dbeafe;">
                        <div class="sp-card-header-left">
                            <div class="sp-card-icon" style="background:#dbeafe; color:#2563eb;"><i class="ti ti-shield-check"></i></div>
                            <h3 class="sp-card-title" style="color:#1d4ed8;">Keputusan Akhir SHE</h3>
                        </div>
                        <span class="sp-badge sp-badge--blue">SHE</span>
                    </div>
                    <div class="sp-card-body">
                        <form class="form-approve" action="<?= e(route('admin.submissions.approve', $submission->id)) ?>" method="post" style="margin-bottom:0.75rem;">
                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                            <button type="button" class="sp-btn sp-btn--success" onclick="showConfirmModal('approve', this)">
                                <i class="ti ti-circle-check"></i> Setujui Pengajuan
                            </button>
                        </form>

                        <div class="sp-she-divider">atau tolak</div>

                        <form class="form-reject" action="<?= e(route('admin.submissions.reject', $submission->id)) ?>" method="post">
                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                            <textarea name="she_notes" class="sp-textarea" rows="3" required placeholder="Tuliskan alasan penolakan..."></textarea>
                            <button type="button" class="sp-btn sp-btn--danger" onclick="showConfirmModal('reject', this)">
                                <i class="ti ti-circle-x"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- /sp-aside -->
    </div><!-- /sp-layout -->
</main>

<script>
    // Custom confirm modal for better UX
    function showConfirmModal(action, triggerBtn) {
        const messages = {
            'forward-hrga': {
                title: 'Ajukan Pengajuan?',
                message: 'Pastikan semua berkas identitas sudah lengkap sebelum diajukan ke tahap berikutnya.',
                confirmText: 'Ya, Ajukan',
                cancelText: 'Batal',
                icon: 'ti-send',
                iconBg: '#dbeafe',
                iconColor: '#2563eb'
            },
            'forward-tod': {
                title: 'Ajukan Hasil TOD ke SHE?',
                message: 'Pastikan hasil tes teori dan tes praktek sudah diunggah sebelum diajukan ke SHE.',
                confirmText: 'Ya, Ajukan ke SHE',
                cancelText: 'Batal',
                icon: 'ti-send',
                iconBg: '#fed7aa',
                iconColor: '#ea580c'
            },
            'approve': {
                title: 'Setujui Pengajuan?',
                message: 'Dengan menyetujui, pengajuan ini akan masuk status "Disetujui" dan tidak dapat diubah lagi.',
                confirmText: 'Ya, Setujui',
                cancelText: 'Batal',
                icon: 'ti-circle-check',
                iconBg: '#d1fae5',
                iconColor: '#10b981'
            },
            'reject': {
                title: 'Tolak Pengajuan?',
                message: 'Pastikan alasan penolakan sudah ditulis dengan jelas di kolom di bawah. Status akan dikembalikan ke tahap sebelumnya.',
                confirmText: 'Ya, Tolak',
                cancelText: 'Batal',
                icon: 'ti-circle-x',
                iconBg: '#fee2e2',
                iconColor: '#dc2626'
            },
            'destroy': {
                title: 'Hapus Pengajuan Secara Permanen?',
                message: 'Tindakan ini tidak dapat dibatalkan. Semua data dan berkas pengajuan akan dihapus selamanya.',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                icon: 'ti-trash',
                iconBg: '#fee2e2',
                iconColor: '#dc2626'
            },
            'delete-file-hrga': {
                title: 'Hapus Berkas Identitas?',
                message: 'Berkas yang dihapus tidak dapat dipulihkan. Anda dapat mengunggah berkas baru setelah ini.',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                icon: 'ti-trash',
                iconBg: '#fee2e2',
                iconColor: '#dc2626'
            },
            'delete-file-tod': {
                title: 'Hapus Berkas Verifikasi Teknis?',
                message: 'Berkas yang dihapus tidak dapat dipulihkan. Anda dapat mengunggah berkas baru setelah ini.',
                confirmText: 'Ya, Hapus',
                cancelText: 'Batal',
                icon: 'ti-trash',
                iconBg: '#fee2e2',
                iconColor: '#dc2626'
            },
            'paramedic-verify': {
                title: 'Ajukan Hasil Verifikasi Medis?',
                message: 'Hasil MCU yang disetujui akan dilanjutkan ke tahap berikutnya sesuai alur.',
                confirmText: 'Ya, Ajukan',
                cancelText: 'Batal',
                icon: 'ti-check',
                iconBg: '#d1fae5',
                iconColor: '#10b981'
            }
        };

        const config = messages[action] || {};

        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'sp-modal-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;

        // Create modal content
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.3s ease-out;
        `;

        modal.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: ${config.iconBg || '#fef3c7'}; border-radius: 50%; padding: 0.75rem; flex-shrink: 0;">
                    <i class="ti ${config.icon || 'ti-help-circle'}" style="color: ${config.iconColor || '#d97706'}; font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 1.125rem; font-weight: 600;">${config.title}</h3>
                    <p style="margin: 0; color: #6b7280; font-size: 0.95rem; line-height: 1.5;">${config.message}</p>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="sp-modal-cancel" style="background: #f3f4f6; color: #374151; padding: 0.75rem 1.25rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.2s;">
                    ${config.cancelText}
                </button>
                <button type="button" class="sp-modal-confirm" style="background: #3b82f6; color: white; padding: 0.75rem 1.25rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.2s;">
                    ${config.confirmText}
                </button>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            .sp-modal-cancel:hover {
                background: #e5e7eb !important;
            }
            .sp-modal-confirm:hover {
                background: #2563eb !important;
            }
        `;
        document.head.appendChild(style);

        // Handle cancel
        modal.querySelector('.sp-modal-cancel').addEventListener('click', function() {
            overlay.remove();
        });

        // Handle confirm
        modal.querySelector('.sp-modal-confirm').addEventListener('click', function() {
            overlay.remove();
            // Submit the corresponding form
            if (action === 'forward-hrga') {
                document.querySelector('.forward-form-hrga').submit();
            } else if (action === 'forward-tod') {
                document.querySelector('.forward-form-tod').submit();
            } else if (action === 'approve') {
                document.querySelector('.form-approve').submit();
            } else if (action === 'reject') {
                document.querySelector('.form-reject').submit();
            } else if (action === 'destroy') {
                document.querySelector('.form-destroy').submit();
            } else if (action === 'paramedic-verify') {
                triggerBtn.closest('form').submit();
            } else if (action === 'delete-file-hrga' || action === 'delete-file-tod') {
                triggerBtn.closest('form').submit();
            }
        });

        // Allow escape key to close
        const escapeHandler = function(e) {
            if (e.key === 'Escape') {
                overlay.remove();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }

    function showEditModal() {
        const modal = document.getElementById('editApplicantModal');
        if (modal) {
            modal.style.display = 'flex';
            // Close on overlay click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeEditModal();
                }
            });
        }
    }

    function closeEditModal() {
        const modal = document.getElementById('editApplicantModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function browseDrive(type) {
        window.open(
            '<?= route('admin.drive-explorer') ?>?mode=pick&target=' + type + '&submission_id=<?= e($submission->id) ?>',
            'drivePicker', 'width=1000,height=700'
        );
    }

    window.onFilePicked = function(url, name, type) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= e(route("admin.submissions.link-drive", $submission->id)) ?>';
        form.innerHTML = `
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="file_type" value="${type}">
            <input type="hidden" name="file_name" value="${name}">
            <input type="hidden" name="file_url"  value="${url}">
        `;
        document.body.appendChild(form);
        form.submit();
    };

    // AJAX upload — no full page refresh, show inline progress
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ajax-upload-form input[type=file]').forEach(function (input) {
            input.addEventListener('change', async function () {
                if (!input.files || !input.files.length) return;

                const form    = input.closest('form');
                const fd      = new FormData(form);
                input.disabled = true;

                // Show progress indicator
                let prog = form.querySelector('.sp-upload-progress');
                if (!prog) {
                    prog = document.createElement('div');
                    prog.className = 'sp-upload-progress';
                    prog.innerHTML = '<div class="sp-spinner"></div><span>Mengunggah...</span>';
                    form.appendChild(prog);
                }
                prog.classList.add('visible');

                try {
                    const res  = await fetch(form.action, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json().catch(() => null);

                    if (data && data.success) {
                        const container = form.closest('.sp-file-item');
                        if (container) {
                            const nameEl = container.querySelector('.sp-file-name');
                            if (nameEl) {
                                nameEl.textContent = input.files[0].name;
                                nameEl.classList.remove('is-empty');
                            }
                            container.classList.remove('is-empty');
                            const lbl = form.querySelector('.sp-upload-label');
                            if (lbl) lbl.textContent = 'Ganti file';
                        }
                        prog.innerHTML = '<i class="ti ti-check" style="color:#16a34a;font-size:0.9rem;"></i><span style="color:#16a34a;">Berhasil diunggah</span>';
                        setTimeout(() => { prog.classList.remove('visible'); }, 1500);
                        
                        // Reload page after brief delay to show success, ensuring files are refreshed from server
                        setTimeout(() => { window.location.reload(); }, 2000);
                    } else {
                        prog.innerHTML = '<i class="ti ti-alert-circle" style="color:#dc2626;font-size:0.9rem;"></i><span style="color:#dc2626;">' + ((data && data.message) || 'Gagal mengunggah') + '</span>';
                        setTimeout(() => { prog.classList.remove('visible'); }, 3000);
                    }
                } catch (err) {
                    console.error(err);
                    prog.innerHTML = '<i class="ti ti-alert-circle" style="color:#dc2626;font-size:0.9rem;"></i><span style="color:#dc2626;">Gagal terhubung ke server</span>';
                    setTimeout(() => { prog.classList.remove('visible'); }, 3000);
                } finally {
                    input.disabled = false;
                    input.value = '';
                }
            });
        });
    });

    // Lazy-load image previews using IntersectionObserver (lightweight)
    (function () {
        if (!('IntersectionObserver' in window)) return; // graceful fallback
        const imgs = document.querySelectorAll('img.sp-thumb[data-src]');
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.getAttribute('data-src');
                    if (src) {
                        // load when browser is idle if available
                        const load = () => { img.src = src; img.removeAttribute('data-src'); };
                        if ('requestIdleCallback' in window) requestIdleCallback(load, {timeout:1000}); else load();
                    }
                    obs.unobserve(img);
                }
            });
        }, {rootMargin: '200px 0px'});
        imgs.forEach(i => io.observe(i));
    })();
</script>
</body>
</html>