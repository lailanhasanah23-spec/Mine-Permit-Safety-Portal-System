@php
    function portal_document_format_datetime(?string $value): string
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

    function portal_document_format_size(int $bytes): string
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
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Akses dokumen kebijakan Mine Permit dan SIMPER (KTP-OHS-102) versi terbaru.">
    <meta name="theme-color" content="#0b1220">
    <title>{{ (string) ($document['title'] ?? 'Dokumen Kebijakan') }} | LCM</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v=2.5">
</head>
<body class="lcm-landing-page">
<a href="#main-content" class="skip-link">Lewati ke konten utama</a>
<header class="topbar">
    <div class="container">
        <a class="brand brand-with-logo" href="{{ route('portal.index') }}" aria-label="SHE APPS Laz Coal Mandiri">
            <img class="brand-logo" src="{{ asset('assets/branding/remote/lcm-logo.png') }}" alt="Logo Laz Coal Mandiri" width="52" height="52">
            <span class="brand-text">
                <span class="brand-kicker">Laz Coal Mandiri (LCM)</span>
                <span class="brand-title">SHE APPS Safety Portal</span>
            </span>
        </a>
        <div class="actions">
            <a class="btn btn-ghost" href="{{ route('portal.forms') }}">Kembali ke Portal Form</a>
            <a class="btn btn-secondary" href="{{ route('admin.login') }}" rel="nofollow">Konsol Admin</a>
        </div>
    </div>
</header>

<main id="main-content" class="container page-main document-page">
    <section class="card card-elevated document-hero">
        <p class="section-eyebrow">Dokumen Resmi</p>
        <h1 class="panel-title">{{ (string) ($document['title'] ?? 'Dokumen Kebijakan') }}</h1>
        <p class="panel-subtitle">{{ (string) ($document['description'] ?? 'Dokumen ini dipublikasikan untuk dibaca seluruh personel terkait.') }}</p>

        <div class="enterprise-note" role="note">
            Dokumen ini merupakan acuan operasional. Pastikan seluruh personel membaca revisi aktif sebelum menjalankan proses Mine Permit dan SIMPER.
        </div>

        <div class="document-meta-grid">
            <div class="card card-quiet">
                <h3>Revisi Aktif</h3>
                <p class="metric-value metric-value-sm">{{ (string) ($document['revision_label'] ?? '-') }}</p>
                <p class="small">Diterbitkan {{ portal_document_format_datetime((string) ($document['revision_created_at'] ?? '')) }}</p>
            </div>
            <div class="card card-quiet">
                <h3>Nama File</h3>
                <p class="small">{{ (string) ($document['original_filename'] ?? '-') }}</p>
                <p class="small">Ukuran {{ portal_document_format_size((int) ($document['file_size'] ?? 0)) }}</p>
            </div>
            <div class="card card-quiet">
                <h3>Akses Cepat</h3>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ route('portal.documents.file', ['code' => (string) ($document['code'] ?? '')]) }}" target="_blank" rel="noopener noreferrer">Buka di Tab Baru</a>
                </div>
            </div>
        </div>
    </section>

    <section class="card card-elevated document-viewer-wrap">
        <h2 class="panel-title">Pratinjau Dokumen</h2>
        <p class="small">Jika pratinjau tidak muncul pada browser Anda, gunakan tombol Buka di Tab Baru.</p>
        @if (!empty($document['revision_notes']))
            <p class="small"><strong>Catatan Revisi Aktif:</strong> {{ (string) $document['revision_notes'] }}</p>
        @endif
        <iframe
            class="document-viewer"
            src="{{ route('portal.documents.file', ['code' => (string) ($document['code'] ?? '')]) }}"
            title="Pratinjau Dokumen {{ (string) ($document['title'] ?? 'Kebijakan') }}"
            loading="lazy"
        ></iframe>
    </section>

    <section class="card table-card">
        <div class="toolbar">
            <div>
                <h2 class="panel-title">Riwayat Revisi</h2>
                <p class="small">Riwayat ditampilkan agar pembaca mengetahui perubahan dokumen dari waktu ke waktu.</p>
            </div>
            <p class="result-count">{{ count($revisions) }} revisi</p>
        </div>

        @if (!$revisions)
            <div class="empty-state">
                <div class="empty-state-icon">i</div>
                <p>Belum ada riwayat revisi yang tersedia.</p>
            </div>
        @else
            <section class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Revisi</th>
                        <th>Tanggal Rilis</th>
                        <th>Nama File</th>
                        <th>Ukuran</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($revisions as $revision)
                        <tr>
                            <td>
                                <span class="badge badge-window {{ ((int) ($revision['id'] ?? 0) === (int) ($document['revision_id'] ?? 0)) ? 'is-live' : 'is-scheduled' }}">{{ (string) ($revision['revision_label'] ?? '-') }}</span>
                            </td>
                            <td>{{ portal_document_format_datetime((string) ($revision['created_at'] ?? '')) }}</td>
                            <td>
                                <span class="small">{{ (string) ($revision['original_filename'] ?? '-') }}</span>
                                @if (!empty($revision['notes']))
                                    <p class="small">{{ (string) $revision['notes'] }}</p>
                                @endif
                            </td>
                            <td>{{ portal_document_format_size((int) ($revision['file_size'] ?? 0)) }}</td>
                            <td>
                                <a class="btn btn-secondary btn-sm" href="{{ route('portal.documents.file', ['code' => (string) ($document['code'] ?? ''), 'revision' => (int) ($revision['id'] ?? 0)]) }}" target="_blank" rel="noopener noreferrer">Buka</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
        @endif
    </section>
</main>

<footer class="footer" id="portal-footer">
    <div class="container footer-grid">
        <div>
            <p class="footer-brand">LAZ Coal Mandiri - Safety Operations Portal</p>
            <p>Gunakan dokumen revisi terbaru sebagai acuan operasional resmi.</p>
        </div>
        <div class="footer-links">
            <a href="{{ route('portal.forms') }}">Kembali ke Portal Form</a>
            <p class="small footer-meta">&copy; {{ date('Y') }} Laz Coal Mandiri. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
