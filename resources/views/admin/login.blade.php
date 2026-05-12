@php
    $error = session('error');
    $success = session('success');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Akses internal administrator untuk pengelolaan formulir keselamatan Laz Coal Mandiri.">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Internal Access Administrator | LCM</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v=2.5">
    <link rel="stylesheet" href="{{ asset('assets/admin-premium.css') }}?v=1.0">
    <style>
        .auth-tabs {
            display: flex;
            gap: 1px;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--color-border-subtle);
        }

        .auth-tab-button {
            flex: 1;
            padding: 0.75rem 1rem;
            background: none;
            border: none;
            color: var(--color-text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -1px;
        }

        .auth-tab-button:hover {
            color: var(--color-text-primary);
            background-color: var(--color-bg-subtle);
        }

        .auth-tab-button.is-active {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
        }

        .auth-tab-panel {
            display: none;
        }

        .auth-tab-panel.is-active {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .vendor-autocomplete {
            position: relative;
        }

        .vendor-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--color-border-subtle);
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .vendor-suggestions.is-visible {
            display: block;
        }

        .vendor-suggestion {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .vendor-suggestion:hover {
            background-color: var(--color-bg-subtle);
        }

        .tab-icon {
            display: inline-block;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="auth-page">
<a href="#main-content" class="skip-link">Lewati ke konten utama</a>
<main id="main-content" class="auth-shell">
    <section class="auth-visual" aria-label="Branding Laz Coal Mandiri">
        <div class="auth-visual-content">
            <img class="auth-logo" src="{{ asset('assets/branding/remote/lcm-logo.png') }}" alt="Logo Laz Coal Mandiri" width="76" height="76">
            <p class="auth-kicker">Laz Coal Mandiri</p>
            <h1 class="auth-title">Safety Operations Console</h1>
            <p class="auth-copy">Portal terpadu untuk administrator sistem, subkon/vendor, dan personel operasional dalam pengelolaan formulir keselamatan, SIMPER, dan Mine Permit.</p>
            <ul class="auth-points">
                <li>Autentikasi terlindungi CSRF dan lockout percobaan login.</li>
                <li>Audit log mencatat perubahan konfigurasi formulir.</li>
                <li>Dukungan untuk multiple user roles dan vendor partners.</li>
            </ul>
        </div>
    </section>

    <section class="auth-form card card-elevated">
        <a class="auth-back" href="{{ route('portal.index.php') }}">Kembali ke Portal Publik</a>
        <p class="section-eyebrow">Portal Access</p>
        <h2 class="panel-title">Akses Konsol Sistem</h2>

        @if ($error)
            <div class="alert alert-error">{{ $error }}</div>
        @endif
        @if ($success)
            <div class="alert alert-success" data-auto-dismiss="3500">{{ $success }}</div>
        @endif

        <!-- Tab Navigation -->
        <div class="auth-tabs">
            <button type="button" class="auth-tab-button is-active" data-tab="admin">
                <span class="tab-icon">🔐</span>Administrator
            </button>
            <button type="button" class="auth-tab-button" data-tab="vendor">
                <span class="tab-icon">🏢</span>Subcon / Vendor
            </button>
        </div>

        <!-- Admin Tab -->
        <div class="auth-tab-panel is-active" data-panel="admin">
            <p class="panel-subtitle">Akses untuk personel administrator dan operasional internal Laz Coal Mandiri.</p>

            <div class="auth-trust-grid" aria-hidden="true">
                <div class="auth-trust-item">
                    <strong>24/7</strong>
                    <span>Monitoring akses</span>
                </div>
                <div class="auth-trust-item">
                    <strong>CSRF</strong>
                    <span>Perlindungan sesi</span>
                </div>
                <div class="auth-trust-item">
                    <strong>Audit</strong>
                    <span>Jejak perubahan</span>
                </div>
            </div>

            <form method="post" action="{{ route('admin.login.submit') }}" class="stack" autocomplete="on">
                @csrf
                <input type="hidden" name="type" value="admin">

                <div class="form-group">
                    <label for="email">Email Administrator</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="username email"
                        spellcheck="false"
                        placeholder="nama@lazcoalmandiri.co.id"
                        data-auth-email
                    >
                    <p class="small auth-input-hint">Gunakan email akun internal yang terdaftar pada sistem administrator.</p>
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="auth-password-wrap">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi akun Anda"
                            data-auth-password
                        >
                        <button class="auth-password-toggle" type="button" data-auth-toggle-password aria-controls="password" aria-pressed="false" title="Tampilkan kata sandi">
                            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.67 8.5 7.607 6 12 6c4.393 0 8.33 2.5 9.964 5.678.14.273.14.576 0 .849C20.33 15.5 16.393 18 12 18c-4.393 0-8.33-2.5-9.964-5.678z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="icon-eye-slash is-hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <p class="small auth-caps is-hidden" data-auth-caps>Caps Lock aktif. Pastikan huruf besar/kecil sesuai.</p>
                </div>
                <label class="auth-inline-control">
                    <input type="checkbox" data-auth-remember-email checked>
                    Ingat email pada perangkat ini
                </label>
                <button class="btn btn-primary btn-block" type="submit">Masuk ke Konsol Administrator</button>
            </form>

            <p class="small auth-shortcut">Tip: tekan <strong>Enter</strong> untuk masuk lebih cepat setelah mengisi email dan kata sandi.</p>

            <div class="auth-quick-access">
                <div class="quick-access-divider">
                    <span>Atau Masuk Cepat (Presentation Mode)</span>
                </div>
                <form action="{{ route('admin.quick-login') }}" method="POST" class="quick-access-form">
                    @csrf
                    <div class="quick-access-group">
                        <select name="role" required class="quick-access-select">
                            <option value="" disabled selected>Pilih Role Akses...</option>
                            <option value="admin">Super Admin (Full Access)</option>
                            <option value="hrga">HRGA (Upload Berkas Identitas)</option>
                            <option value="paramedic">Paramedic (Verifikasi MCU)</option>
                            <option value="tod">TOD (Upload Tes Teknis)</option>
                            <option value="she">SHE (Approval & GSuite)</option>
                        </select>
                        <button type="submit" class="btn btn-secondary btn-quick">Masuk Sekarang</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vendor Tab -->
        <div class="auth-tab-panel" data-panel="vendor">
            <p class="panel-subtitle">Akses untuk mitra kerja (subkon/vendor) guna mengelola pengajuan SIMPER dan Mine Permit.</p>

            <div class="auth-trust-grid" aria-hidden="true">
                <div class="auth-trust-item">
                    <strong>Aman</strong>
                    <span>Verifikasi perusahaan</span>
                </div>
                <div class="auth-trust-item">
                    <strong>Cepat</strong>
                    <span>Login sederhana</span>
                </div>
                <div class="auth-trust-item">
                    <strong>Terpercaya</strong>
                    <span>Proteksi data</span>
                </div>
            </div>

            <form method="post" action="{{ route('admin.login.submit') }}" class="stack" autocomplete="off">
                @csrf
                <input type="hidden" name="type" value="vendor">

                <div class="form-group vendor-autocomplete">
                    <label for="company_name">Nama Perusahaan / PT</label>
                    <input
                        id="company_name"
                        name="company_name"
                        type="text"
                        required
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="Cari nama perusahaan Anda..."
                        data-vendor-input
                    >
                    <div class="vendor-suggestions" data-vendor-list>
                        @foreach($vendors as $vendor)
                            <div class="vendor-suggestion" data-vendor="{{ $vendor }}">{{ $vendor }}</div>
                        @endforeach
                    </div>
                    <p class="small auth-input-hint">Pilih nama perusahaan Anda dari daftar yang tersedia.</p>
                </div>

                @unless(config('legacy_auth.vendor_passwordless', true))
                <div class="form-group">
                    <label for="vendor_password">Kata Sandi Perusahaan</label>
                    <div class="auth-password-wrap">
                        <input
                            id="vendor_password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi perusahaan"
                            data-vendor-password
                        >
                        <button class="auth-password-toggle" type="button" data-vendor-toggle-password aria-controls="vendor_password" aria-pressed="false" title="Tampilkan kata sandi">
                            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.67 8.5 7.607 6 12 6c4.393 0 8.33 2.5 9.964 5.678.14.273.14.576 0 .849C20.33 15.5 16.393 18 12 18c-4.393 0-8.33-2.5-9.964-5.678z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="icon-eye-slash is-hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>
                @else
                <input type="hidden" name="password" value="">
                <div class="form-group">
                    <p class="small">Masuk tanpa kata sandi: cukup pilih nama perusahaan Anda.</p>
                </div>
                @endunless

                <button class="btn btn-primary btn-block" type="submit">Masuk sebagai Vendor</button>
            </form>

            <p class="small auth-note">Jika Anda belum terdaftar atau lupa kata sandi perusahaan, silakan hubungi administrator Laz Coal Mandiri.</p>
        </div>

        <p class="small auth-note">Halaman ini menggunakan enkripsi HTTPS dan dilindungi oleh protokol keamanan internasional.</p>
    </section>
</main>

<script src="{{ asset('assets/app.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabButtons = document.querySelectorAll('.auth-tab-button');
        const tabPanels = document.querySelectorAll('.auth-tab-panel');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                tabButtons.forEach(btn => btn.classList.remove('is-active'));
                tabPanels.forEach(panel => panel.classList.remove('is-active'));
                this.classList.add('is-active');
                document.querySelector(`[data-panel="${tabName}"]`).classList.add('is-active');
            });
        });

        // Vendor autocomplete
        const vendorInput = document.querySelector('[data-vendor-input]');
        const vendorList = document.querySelector('[data-vendor-list]');
        const vendorSuggestions = document.querySelectorAll('.vendor-suggestion');

        if (vendorInput) {
            vendorInput.addEventListener('input', function() {
                const value = this.value.toLowerCase();
                if (value.length === 0) {
                    vendorList.classList.remove('is-visible');
                    return;
                }
                vendorSuggestions.forEach(suggestion => {
                    const text = suggestion.textContent.toLowerCase();
                    suggestion.style.display = text.includes(value) ? 'block' : 'none';
                });
                vendorList.classList.add('is-visible');
            });

            vendorInput.addEventListener('focus', function() {
                if (this.value.length > 0) {
                    vendorList.classList.add('is-visible');
                }
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.vendor-autocomplete')) {
                    vendorList.classList.remove('is-visible');
                }
            });

            vendorSuggestions.forEach(suggestion => {
                suggestion.addEventListener('click', function() {
                    vendorInput.value = this.textContent;
                    vendorList.classList.remove('is-visible');
                });
            });
        }

        // Password toggles
        document.querySelectorAll('[data-auth-toggle-password], [data-vendor-toggle-password]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const fieldSelector = this.getAttribute('data-auth-toggle-password') ? '[data-auth-password]' : '[data-vendor-password]';
                const field = document.querySelector(fieldSelector);
                const isPassword = field.type === 'password';
                field.type = isPassword ? 'text' : 'password';
                this.setAttribute('aria-pressed', isPassword);
                this.querySelector('.icon-eye').classList.toggle('is-hidden');
                this.querySelector('.icon-eye-slash').classList.toggle('is-hidden');
            });
        });

        // Caps lock detection
        const passwordField = document.querySelector('[data-auth-password]');
        const capsLockWarning = document.querySelector('[data-auth-caps]');
        if (passwordField && capsLockWarning) {
            passwordField.addEventListener('keydown', function(e) {
                const capsLockOn = e.getModifierState && e.getModifierState('CapsLock');
                capsLockWarning.classList.toggle('is-hidden', !capsLockOn);
            });
        }

        // Remember email
        const emailField = document.querySelector('[data-auth-email]');
        const rememberCheckbox = document.querySelector('[data-auth-remember-email]');
        if (emailField && rememberCheckbox) {
            const savedEmail = localStorage.getItem('auth-email');
            if (savedEmail) {
                emailField.value = savedEmail;
                rememberCheckbox.checked = true;
            }
            rememberCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    localStorage.setItem('auth-email', emailField.value);
                } else {
                    localStorage.removeItem('auth-email');
                }
            });
            emailField.addEventListener('change', function() {
                if (rememberCheckbox.checked) {
                    localStorage.setItem('auth-email', this.value);
                }
            });
        }

        // Auto dismiss success alert
        const successAlert = document.querySelector('[data-auto-dismiss]');
        if (successAlert) {
            const duration = parseInt(successAlert.dataset.autoDismiss) || 3500;
            setTimeout(() => {
                successAlert.style.animation = 'fadeOut 0.5s ease forwards';
                setTimeout(() => successAlert.remove(), 500);
            }, duration);
        }
    });
</script>
</body>
</html>
