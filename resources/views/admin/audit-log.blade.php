<?php
function audit_decode_json_state(?string $rawJson): ?array
{
    if ($rawJson === null || trim($rawJson) === '') {
        return null;
    }

    $decoded = json_decode($rawJson, true);
    return is_array($decoded) ? $decoded : null;
}

function audit_compact_change_summary(?array $beforeState, ?array $afterState): string
{
    if ($beforeState === null && $afterState === null) {
        return 'Tidak ada detail perubahan.';
    }

    if ($beforeState === null) {
        return 'Pembuatan data baru.';
    }

    if ($afterState === null) {
        return 'Data dihapus atau tidak tersedia setelah proses.';
    }

    $keys = array_unique(array_merge(array_keys($beforeState), array_keys($afterState)));
    $changed = [];

    foreach ($keys as $key) {
        $beforeValue = $beforeState[$key] ?? null;
        $afterValue = $afterState[$key] ?? null;

        if ($beforeValue !== $afterValue) {
            $changed[] = (string) $key;
        }
    }

    if ($changed === []) {
        return 'Tidak ada perubahan nilai field.';
    }

    if (count($changed) <= 5) {
        return 'Field berubah: ' . implode(', ', $changed) . '.';
    }

    return 'Field berubah: ' . implode(', ', array_slice($changed, 0, 5)) . ' dan lainnya.';
}

function audit_format_datetime(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return '-';
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
    if (!$dt) {
        return $value;
    }

    return $dt->format('d M Y H:i:s');
}

function audit_format_actor(array $row): string
{
    $name = trim((string) ($row['actor_name'] ?? ''));
    $email = trim((string) ($row['actor_email'] ?? ''));

    if ($name !== '' && $email !== '') {
        return $name . ' (' . $email . ')';
    }

    if ($email !== '') {
        return $email;
    }

    if ($name !== '') {
        return $name;
    }

    return 'System';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log Administrator</title>
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
    <a class="is-active" href="<?= e(route('admin.audit-log.php')) ?>">Audit Log</a>
    <a href="<?= e(route('admin.documents.php')) ?>">Dokumen Kebijakan</a>
    <a href="<?= e(route('admin.email-submissions.php')) ?>">Email SIMPER</a>
    <?php endif; ?>

    <a href="<?= e(route('admin.change-password.php')) ?>">Ubah Kata Sandi</a>
</nav>

<main id="main-content" class="container page-main">
    <section class="card card-elevated">
        <p class="section-eyebrow">Governance & Traceability</p>
        <h1 class="panel-title">Audit Log Aktivitas Administrator</h1>
        <p class="panel-subtitle">Pantau setiap perubahan konfigurasi untuk menjaga akuntabilitas operasional dan kepatuhan proses.</p>
    </section>

    <section class="card table-card">
        <div class="toolbar">
            <div>
                <h2 class="panel-title">Filter Audit</h2>
                <p class="small">Gunakan kombinasi filter untuk menemukan jejak perubahan lebih cepat.</p>
            </div>
            <form method="get" class="audit-filter-form" aria-label="Filter audit log">
                <input
                    type="search"
                    name="q"
                    class="input-search"
                    placeholder="Cari aksi, entitas, email, IP, id..."
                    value="<?= e($filters['q']) ?>"
                    aria-label="Cari audit log"
                    data-filter-input
                    data-filter-target="#auditLogTableBody tr"
                >
                <div class="filter-row mt-sm">
                    <select name="action" aria-label="Filter aksi audit">
                        <option value="">Semua Aksi</option>
                        <?php foreach ($options['actions'] as $action): ?>
                            <option value="<?= e($action) ?>" <?= $filters['action'] === $action ? 'selected' : '' ?>><?= e($action) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="entity_type" aria-label="Filter jenis entitas">
                        <option value="">Semua Entitas</option>
                        <?php foreach ($options['entity_types'] as $entityType): ?>
                            <option value="<?= e($entityType) ?>" <?= $filters['entity_type'] === $entityType ? 'selected' : '' ?>><?= e($entityType) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-row mt-sm">
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    <a class="btn btn-secondary" href="<?= e(route('admin.audit-log.php')) ?>">Reset</a>
                </div>
            </form>
        </div>

        <p class="result-count mt-sm" data-filter-count aria-live="polite"><?= e((string) count($logs)) ?> entri ditampilkan</p>

        <section class="table-wrap mt-sm">
            <table>
                <caption class="small table-caption">Riwayat aktivitas terbaru administrator. Menampilkan maksimal 200 entri terbaru.</caption>
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Aksi</th>
                    <th>Entitas</th>
                    <th>Pelaku</th>
                    <th>IP</th>
                    <th>Ringkasan</th>
                    <th>Detail</th>
                </tr>
                </thead>
                <tbody id="auditLogTableBody">
                <?php if (!$logs): ?>
                    <tr>
                        <td colspan="7" class="small">Belum ada data audit log untuk filter yang dipilih.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $beforeState = audit_decode_json_state($log['before_state'] ?? null);
                        $afterState = audit_decode_json_state($log['after_state'] ?? null);
                        $summary = audit_compact_change_summary($beforeState, $afterState);
                        $actionClass = str_contains((string) $log['action'], 'create') ? 'is-live' : (str_contains((string) $log['action'], 'archive') ? 'is-inactive' : 'is-scheduled');
                        ?>
                        <tr>
                            <td><span class="small"><?= e(audit_format_datetime((string) ($log['created_at'] ?? ''))) ?></span></td>
                            <td><span class="badge badge-window <?= e($actionClass) ?>"><?= e((string) ($log['action'] ?? '-')) ?></span></td>
                            <td>
                                <span><?= e((string) ($log['entity_type'] ?? '-')) ?></span>
                                <span class="small">ID: <?= e((string) ($log['entity_id'] ?? '-')) ?></span>
                            </td>
                            <td><span class="small"><?= e(audit_format_actor($log)) ?></span></td>
                            <td><span class="small"><?= e((string) ($log['ip_address'] ?? '-')) ?></span></td>
                            <td><span class="small"><?= e($summary) ?></span></td>
                            <td>
                                <details>
                                    <summary>Lihat JSON</summary>
                                    <div class="audit-json-wrap mt-sm">
                                        <p class="small"><strong>Before</strong></p>
                                        <pre class="audit-json"><?= e($beforeState !== null ? json_encode($beforeState, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null') ?></pre>
                                        <p class="small"><strong>After</strong></p>
                                        <pre class="audit-json"><?= e($afterState !== null ? json_encode($afterState, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null') ?></pre>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <p class="small is-hidden mt-sm" data-filter-empty>Tidak ada audit log yang sesuai dengan kata kunci pencarian.</p>
    </section>
</main>

<script src="<?= e(asset('assets/app.js')) ?>"></script>
</body>
</html>

