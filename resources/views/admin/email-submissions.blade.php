<?php
$success = $success ?? session('success');
$error = $error ?? session('error');

function email_submission_type_label(string $type): string
{
    if ($type === 'general') {
        return 'General';
    }

    return $type === 'new_hire' ? 'New Hire' : 'Perpanjangan';
}

function email_submission_status_badge(string $status): array
{
    if ($status === 'sent') {
        return ['label' => 'Sent', 'class' => 'is-live'];
    }

    if ($status === 'failed') {
        return ['label' => 'Failed', 'class' => 'is-expired'];
    }

    return ['label' => 'Draft', 'class' => 'is-scheduled'];
}

function email_submission_datetime(?string $value): string
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
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Email Pengajuan SIMPER | LCM</title>
    <link rel="stylesheet" href="<?= e(asset('assets/app.css')) ?>?v=4.0">
    <link rel="stylesheet" href="<?= e(asset('assets/admin-premium.css')) ?>?v=1.0">
</head>
<body class="admin-email-submissions-page">
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
    <a class="is-active" href="<?= e(route('admin.email-submissions.php')) ?>">Email SIMPER</a>
    <a href="<?= e(route('admin.drive-explorer')) ?>">Google Drive Explorer</a>
    <?php endif; ?>

    <a href="<?= e(route('admin.change-password.php')) ?>">Ubah Kata Sandi</a>
</nav>

<main id="main-content" class="container page-main">
    <?php if ($success): ?>
        <div class="alert alert-success" data-auto-dismiss="4500"><?= e((string) $success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e((string) $error) ?></div>
    <?php endif; ?>
    <?php if ($errors->any()): ?>
        <div class="alert alert-error"><?= e((string) $errors->first()) ?></div>
    <?php endif; ?>

    <section class="card card-elevated">
        <p class="section-eyebrow">SIMPER Email Workflow</p>
        <h1 class="panel-title">Pengajuan Perpanjangan & New Hire via Gmail</h1>
        <p class="panel-subtitle">Kirim email pengajuan secara cepat dengan lampiran berkas wajib, template siap pakai, serta monitoring status pengiriman.</p>
        <div class="enterprise-note" role="note">
            Gunakan placeholder template: <strong>@{{applicant_name}}</strong>, <strong>@{{company_name}}</strong>, <strong>@{{reference_no}}</strong>,
            <strong>@{{submission_type_label}}</strong>, <strong>@{{request_date}}</strong>.
        </div>
    </section>

    <section class="grid cols-3 admin-metrics-grid admin-email-mini-grid">
        <article class="card card-quiet admin-mini-card">
            <p class="section-eyebrow">Alur Kerja</p>
            <h3>Compose - Template - Kirim - Monitor</h3>
            <p class="small">Gunakan draft untuk menyiapkan data, lalu kirim hanya ketika lampiran wajib sudah siap.</p>
        </article>
        <article class="card card-quiet admin-mini-card">
            <p class="section-eyebrow">Akses Cepat</p>
            <h3>Draft dan Kirim Sekarang</h3>
            <p class="small">Draft menyimpan data tanpa validasi lampiran penuh. Kirim sekarang memaksa SIM, KTP, FU, dan MCU.</p>
        </article>
        <article class="card card-quiet admin-mini-card">
            <p class="section-eyebrow">Monitoring</p>
            <h3>Filter Status</h3>
            <p class="small">Pantau draft, sent, dan failed dari satu tabel operasional yang sama.</p>
        </article>
    </section>

    <section class="grid cols-3 admin-metrics-grid">
        <article class="card card-accent">
            <h3>Total Pengajuan Email</h3>
            <p class="metric-value"><?= e((string) ($summary['total'] ?? 0)) ?></p>
            <p class="metric-help">Seluruh riwayat compose email SIMPER.</p>
        </article>
        <article class="card card-accent">
            <h3>Draft</h3>
            <p class="metric-value"><?= e((string) ($summary['draft'] ?? 0)) ?></p>
            <p class="metric-help">Menunggu review sebelum dikirim.</p>
        </article>
        <article class="card card-accent">
            <h3>Sent / Failed</h3>
            <p class="metric-value"><?= e((string) ($summary['sent'] ?? 0)) ?> / <?= e((string) ($summary['failed'] ?? 0)) ?></p>
            <p class="metric-help">Monitoring hasil kirim Gmail SMTP.</p>
        </article>
    </section>

    <section class="layout-split admin-email-layout">
        <article class="card form-card">
            <h2 class="panel-title">Compose Pengajuan</h2>
            <p class="small">Lampiran utama: SIM, KTP, FU, MCU. Bisa tambah lampiran lain bila diperlukan.</p>
            <p class="small"><strong>Validasi kirim:</strong> tombol <strong>Simpan & Kirim Sekarang</strong> mewajibkan file SIM, KTP, FU, dan MCU.</p>

            <form method="post" action="<?= e(route('admin.email-submissions.store.php')) ?>" enctype="multipart/form-data" class="stack" data-email-submission-form>
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">

                <div class="grid cols-2">
                    <div class="form-group">
                        <label for="submission_type">Jenis Pengajuan</label>
                        <select id="submission_type" name="submission_type" required>
                            <option value="perpanjangan">Perpanjangan</option>
                            <option value="new_hire">New Hire</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="template_id">Template Email</label>
                        <select id="template_id" name="template_id">
                            <option value="">Tanpa Template</option>
                            <?php foreach ($templates as $template): ?>
                                <?php if ((int) ($template['is_active'] ?? 0) !== 1) {
                                    continue;
                                } ?>
                                <option
                                    value="<?= e((string) ($template['id'] ?? 0)) ?>"
                                    data-recipient-cc-template="<?= e((string) ($template['recipient_cc'] ?? '')) ?>"
                                    data-recipient-bcc-template="<?= e((string) ($template['recipient_bcc'] ?? '')) ?>"
                                    data-subject-template="<?= e((string) ($template['subject_template'] ?? '')) ?>"
                                    data-body-template="<?= e((string) ($template['body_template'] ?? '')) ?>"
                                ><?= e((string) ($template['template_name'] ?? '-')) ?> (<?= e((string) ($template['submission_type'] ?? 'general')) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="email-submission-compact-note">
                    <p class="small"><strong>Tip:</strong> pilih template terlebih dahulu agar subject dan body terisi otomatis, lalu sesuaikan detail penerima bila perlu.</p>
                </div>

                <div class="grid cols-2">
                    <div class="form-group">
                        <label for="applicant_name">Nama Applicant</label>
                        <input id="applicant_name" name="applicant_name" maxlength="120" required>
                    </div>
                    <div class="form-group">
                        <label for="company_name">Perusahaan</label>
                        <input id="company_name" name="company_name" maxlength="190">
                    </div>
                </div>

                <div class="form-group">
                    <label for="reference_no">No Referensi / Ticket</label>
                    <input id="reference_no" name="reference_no" maxlength="80" placeholder="Opsional">
                </div>

                <div class="form-group">
                    <label for="recipient_to">To (pisahkan dengan koma)</label>
                    <input id="recipient_to" name="recipient_to" maxlength="500" required placeholder="tim@sapkon.com, supervisor@company.com">
                </div>

                <div class="grid cols-2">
                    <div class="form-group">
                        <label for="recipient_cc">CC</label>
                        <input id="recipient_cc" name="recipient_cc" maxlength="500" placeholder="Opsional">
                    </div>
                    <div class="form-group">
                        <label for="recipient_bcc">BCC</label>
                        <input id="recipient_bcc" name="recipient_bcc" maxlength="500" placeholder="Opsional">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email_subject">Subject Email</label>
                    <input id="email_subject" name="email_subject" maxlength="190" placeholder="Isi manual atau biarkan kosong bila pakai template">
                </div>

                <div class="form-group">
                    <label for="email_body">Body Email</label>
                    <textarea id="email_body" name="email_body" rows="8" maxlength="20000" placeholder="Isi manual atau biarkan kosong bila pakai template"></textarea>
                </div>

                <div class="grid cols-2">
                    <div class="form-group">
                        <label for="doc_sim">Lampiran SIM <span class="small">(wajib saat kirim)</span></label>
                        <input id="doc_sim" name="doc_sim" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-group">
                        <label for="doc_ktp">Lampiran KTP <span class="small">(wajib saat kirim)</span></label>
                        <input id="doc_ktp" name="doc_ktp" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>

                <div class="grid cols-2">
                    <div class="form-group">
                        <label for="doc_fu">Lampiran FU <span class="small">(wajib saat kirim)</span></label>
                        <input id="doc_fu" name="doc_fu" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-group">
                        <label for="doc_mcu">Lampiran MCU <span class="small">(wajib saat kirim)</span></label>
                        <input id="doc_mcu" name="doc_mcu" type="file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>

                <div class="form-group">
                    <label for="doc_other">Lampiran Lain (boleh lebih dari satu)</label>
                    <input id="doc_other" name="doc_other[]" type="file" accept=".pdf,.jpg,.jpeg,.png" multiple>
                </div>

                <div class="actions">
                    <button class="btn btn-secondary" type="submit" name="action_intent" value="save_draft">Simpan Draft</button>
                    <button class="btn btn-primary" type="submit" name="action_intent" value="send_now">Simpan & Kirim Sekarang</button>
                </div>
            </form>
        </article>

        <article class="card table-card">
            <div class="toolbar">
                <div>
                    <h2 class="panel-title">Template Email</h2>
                    <p class="small">Buat template terstandar agar compose email berulang lebih cepat dan konsisten.</p>
                </div>
                <p class="result-count"><?= e((string) count($templates)) ?> template</p>
            </div>

            <div class="enterprise-note admin-template-note" role="note">
                Template aktif dipakai untuk mempercepat pembuatan email. Subject dan body bisa diisi otomatis saat template dipilih.
            </div>

            <form method="post" action="<?= e(route('admin.email-submissions.templates.store.php')) ?>" class="stack">
                <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                <div class="grid cols-2">
                    <div class="form-group">
                        <label for="template_name">Nama Template</label>
                        <input id="template_name" name="template_name" maxlength="190" required>
                    </div>
                    <div class="form-group">
                        <label for="template_type">Tipe Pengajuan</label>
                        <select id="template_type" name="submission_type" required>
                            <option value="general">General</option>
                            <option value="perpanjangan">Perpanjangan</option>
                            <option value="new_hire">New Hire</option>
                        </select>
                    </div>
                </div>
                <div class="grid cols-2">
                    <div class="form-group">
                        <label for="template_recipient_cc">CC Template <span class="small">(opsional)</span></label>
                        <input id="template_recipient_cc" name="recipient_cc" maxlength="500" placeholder="contoh: cc@sapkon.com">
                    </div>
                    <div class="form-group">
                        <label for="template_recipient_bcc">BCC Template <span class="small">(opsional)</span></label>
                        <input id="template_recipient_bcc" name="recipient_bcc" maxlength="500" placeholder="contoh: bcc@sapkon.com">
                    </div>
                </div>
                <div class="form-group">
                    <label for="subject_template">Subject Template</label>
                    <input id="subject_template" name="subject_template" maxlength="190" required>
                </div>
                <div class="form-group">
                    <label for="body_template">Body Template</label>
                    <textarea id="body_template" name="body_template" rows="5" maxlength="20000" required></textarea>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" checked> Template aktif</label>
                </div>
                <button class="btn btn-primary" type="submit">Simpan Template</button>
            </form>

            <section class="table-wrap mt-sm">
                <table>
                    <thead>
                    <tr>
                        <th>Template</th>
                        <th>Tipe</th>
                        <th>CC / BCC</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$templates): ?>
                        <tr><td colspan="5"><div class="empty-state"><div class="empty-state-icon">i</div><p>Belum ada template email.</p></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td>
                                    <p><strong><?= e((string) ($template['template_name'] ?? '-')) ?></strong></p>
                                    <p class="small">Kode: <?= e((string) ($template['template_code'] ?? '-')) ?></p>
                                </td>
                                <td><?= e(email_submission_type_label((string) ($template['submission_type'] ?? 'general'))) ?></td>
                                <td>
                                    <p class="small">CC: <?= e(trim((string) ($template['recipient_cc'] ?? '')) !== '' ? (string) ($template['recipient_cc'] ?? '-') : '-') ?></p>
                                    <p class="small">BCC: <?= e(trim((string) ($template['recipient_bcc'] ?? '')) !== '' ? (string) ($template['recipient_bcc'] ?? '-') : '-') ?></p>
                                </td>
                                <td>
                                    <?php if ((int) ($template['is_active'] ?? 0) === 1): ?>
                                        <span class="badge badge-window is-live">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-window is-inactive">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(email_submission_datetime((string) ($template['updated_at'] ?? ''))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </article>
    </section>

    <section class="card table-card">
        <div class="toolbar">
            <div>
                <h2 class="panel-title">Monitoring Pengiriman Email</h2>
                <p class="small">Filter status dan tipe untuk monitoring progres pengajuan email SIMPER.</p>
            </div>
            <p class="result-count">Tabel bawah dipakai untuk review status kirim harian.</p>
        </div>

        <form method="get" action="<?= e(route('admin.email-submissions.php')) ?>" class="toolbar-controls">
            <div class="filter-row">
                <select name="submission_type" aria-label="Filter tipe pengajuan">
                    <option value="">Semua Tipe</option>
                    <option value="perpanjangan" <?= ($filters['submission_type'] ?? '') === 'perpanjangan' ? 'selected' : '' ?>>Perpanjangan</option>
                    <option value="new_hire" <?= ($filters['submission_type'] ?? '') === 'new_hire' ? 'selected' : '' ?>>New Hire</option>
                </select>
                <select name="status" aria-label="Filter status kirim">
                    <option value="">Semua Status</option>
                    <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
                <input type="date" name="date_from" value="<?= e((string) ($filters['date_from'] ?? '')) ?>" aria-label="Filter tanggal awal">
                <input type="date" name="date_to" value="<?= e((string) ($filters['date_to'] ?? '')) ?>" aria-label="Filter tanggal akhir">
                <input type="search" name="q" placeholder="Cari nama, perusahaan, ref, subject" value="<?= e((string) ($filters['q'] ?? '')) ?>" aria-label="Cari data email submission">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
            </div>
        </form>

        <section class="table-wrap mt-sm">
            <table class="forms-management-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipe</th>
                    <th>Applicant</th>
                    <th>Perusahaan</th>
                    <th>Penerima</th>
                    <th>Status</th>
                    <th>Lampiran</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$submissions): ?>
                    <tr><td colspan="9"><div class="empty-state"><div class="empty-state-icon">i</div><p>Belum ada data pengajuan email.</p></div></td></tr>
                <?php else: ?>
                    <?php foreach ($submissions as $submission): ?>
                        <?php $statusBadge = email_submission_status_badge((string) ($submission['status'] ?? 'draft')); ?>
                        <tr>
                            <td>#<?= e((string) ($submission['id'] ?? 0)) ?></td>
                            <td><?= e(email_submission_type_label((string) ($submission['submission_type'] ?? 'perpanjangan'))) ?></td>
                            <td>
                                <p><strong><?= e((string) ($submission['applicant_name'] ?? '-')) ?></strong></p>
                                <?php if (trim((string) ($submission['reference_no'] ?? '')) !== ''): ?>
                                    <p class="small">Ref: <?= e((string) ($submission['reference_no'] ?? '-')) ?></p>
                                <?php endif; ?>
                            </td>
                            <td><?= e((string) ($submission['company_name'] ?? '-')) ?></td>
                            <td>
                                <p class="small"><?= e((string) ($submission['recipient_to'] ?? '-')) ?></p>
                                <p class="small">Template: <?= e((string) ($submission['template_name'] ?? '-')) ?></p>
                            </td>
                            <td>
                                <span class="badge badge-window <?= e((string) ($statusBadge['class'] ?? 'is-scheduled')) ?>"><?= e((string) ($statusBadge['label'] ?? 'Draft')) ?></span>
                                <?php if ((string) ($submission['status'] ?? '') === 'failed' && trim((string) ($submission['last_error'] ?? '')) !== ''): ?>
                                    <p class="small mt-sm"><?= e((string) ($submission['last_error'] ?? '')) ?></p>
                                <?php endif; ?>
                            </td>
                            <td><?= e((string) ($attachmentCountBySubmission[(int) ($submission['id'] ?? 0)] ?? 0)) ?> file</td>
                            <td>
                                <p class="small">Create: <?= e(email_submission_datetime((string) ($submission['created_at'] ?? ''))) ?></p>
                                <p class="small">Sent: <?= e(email_submission_datetime((string) ($submission['sent_at'] ?? ''))) ?></p>
                            </td>
                            <td>
                                <?php if ((string) ($submission['status'] ?? '') !== 'sent'): ?>
                                    <form method="post" action="<?= e(route('admin.email-submissions.send.php', ['id' => (int) ($submission['id'] ?? 0)])) ?>">
                                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                        <button class="btn btn-secondary btn-sm" type="submit">Kirim Ulang / Kirim</button>
                                    </form>
                                <?php else: ?>
                                    <span class="small">Terkirim</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </section>

    <section class="card card-elevated mt-lg">
        <h2 class="panel-title">Sistem Integrasi Gateway</h2>
        <p class="small" style="color: var(--admin-text-muted); margin-bottom: 0;">Infrastruktur pengiriman email menggunakan <strong>Google Workspace API (OAuth2)</strong> untuk menjamin deliverability tingkat enterprise.</p>
        
        <div class="integration-card <?= $isGoogleLinked ? 'is-active' : 'is-inactive' ?>">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="status-icon">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $isGoogleLinked ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' ?>"></path></svg>
                </div>
                <div>
                    <p style="font-weight: 700; color: var(--admin-secondary); margin-bottom: 0.25rem;">Google Workspace Integration</p>
                    <p style="font-size: 0.85rem; color: var(--admin-text-muted); margin: 0;"><?= $isGoogleLinked ? 'Terkoneksi dengan aman. Sistem penjadwalan token aktif.' : 'Belum terkoneksi. Otorisasi diperlukan untuk mengaktifkan antrean pengiriman.' ?></p>
                </div>
            </div>
            <div>
                <a href="<?= e(route('admin.google.auth', ['service' => 'gmail'])) ?>" class="btn <?= $isGoogleLinked ? 'btn-secondary' : 'btn-primary' ?>" style="padding: 0.6rem 1.2rem;">
                    <?= $isGoogleLinked ? 'Sinkronisasi Ulang' : 'Otorisasi Gateway' ?>
                </a>
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; padding-top: 1rem; border-top: 1px solid var(--admin-border);">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?= $isGoogleLinked ? 'var(--admin-success)' : 'var(--admin-warning)' ?>;"></span>
                <span class="small" style="font-weight: 600; color: var(--admin-text-muted);">Status Gateway: <?= $isGoogleLinked ? 'Online' : 'Offline' ?></span>
            </div>
            <span class="small" style="color: var(--admin-text-muted);">Protocol: <strong><?= e(strtoupper((string) $defaultMailer)) ?> API</strong></span>
        </div>
    </section>
</main>

<script defer src="<?= e(asset('assets/app.js')) ?>?v=4.0"></script>
<script>
    (function () {
        var composeForm = document.querySelector('[data-email-submission-form]');
        var sendNowButton = composeForm ? composeForm.querySelector('button[name="action_intent"][value="send_now"]') : null;
        var draftButton = composeForm ? composeForm.querySelector('button[name="action_intent"][value="save_draft"]') : null;
        var requiredAttachmentIds = ['doc_sim', 'doc_ktp', 'doc_fu', 'doc_mcu'];

        function setRequiredAttachments(isRequired) {
            requiredAttachmentIds.forEach(function (id) {
                var input = document.getElementById(id);
                if (!input) {
                    return;
                }
                input.required = isRequired;
            });
        }

        if (sendNowButton) {
            sendNowButton.addEventListener('click', function () {
                setRequiredAttachments(true);
            });
        }

        if (draftButton) {
            draftButton.addEventListener('click', function () {
                setRequiredAttachments(false);
            });
        }

        var templateSelect = document.getElementById('template_id');
        var subjectInput = document.getElementById('email_subject');
        var bodyInput = document.getElementById('email_body');
        var ccInput = document.getElementById('recipient_cc');
        var bccInput = document.getElementById('recipient_bcc');

        if (!templateSelect || !subjectInput || !bodyInput) {
            return;
        }

        templateSelect.addEventListener('change', function () {
            var selectedOption = templateSelect.options[templateSelect.selectedIndex];
            if (!selectedOption) {
                return;
            }

            if (subjectInput.value.trim() === '') {
                subjectInput.value = selectedOption.getAttribute('data-subject-template') || '';
            }

            if (bodyInput.value.trim() === '') {
                bodyInput.value = selectedOption.getAttribute('data-body-template') || '';
            }

            var templateCc = selectedOption.getAttribute('data-recipient-cc-template') || '';
            var templateBcc = selectedOption.getAttribute('data-recipient-bcc-template') || '';

            if (ccInput && ccInput.value.trim() === '' && templateCc !== '') {
                ccInput.value = templateCc;
            }

            if (bccInput && bccInput.value.trim() === '' && templateBcc !== '') {
                bccInput.value = templateBcc;
            }
        });
    })();
</script>
</body>
</html>
