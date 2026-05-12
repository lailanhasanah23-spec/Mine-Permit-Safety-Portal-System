<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal SIMPER & Mine Permit | SHE LCM</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; }
        .font-oswald { font-family: 'Oswald', sans-serif; }
        .red-block {
            position: fixed;
            top: 0;
            right: 0;
            width: 18vw;
            min-width: 140px;
            height: 100vh;
            background: #9e1b22;
            border-top-left-radius: 120px;
            z-index: 0;
            pointer-events: none;
        }
        .col-divider {
            border-right: 1px solid #f1f5f9;
        }
        @media (max-width: 1024px) {
            .col-divider { border-right: none; }
            .red-block { display: none; }
        }
        .btn-outlined {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
            padding: 14px 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #1e40af;
            background: #fff;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            line-height: 1.2;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .btn-outlined:hover {
            border-color: #1e40af;
            background: #1e40af;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(30, 64, 175, 0.15);
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-container {
            padding-left: 56px;
            padding-right: calc(18vw + 56px);
        }
        @media (max-width: 1024px) {
            .page-container {
                padding-left: 24px;
                padding-right: 24px;
            }
        }

        /* Admin login button - minimal professional look */
        .admin-login-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: rgba(255, 255, 255, 0.82);
            transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease;
        }
        .admin-login-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.28);
            color: #ffffff;
        }
        .admin-login-btn:focus { box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.18); outline: none; }
        .admin-login-icon { display: inline-flex; align-items: center; justify-content: center; opacity: 0.75; transition: opacity 160ms ease; }
        .admin-login-svg { width: 14px; height: 14px; display: block; }
        .admin-login-btn:hover .admin-login-icon { opacity: 0.95; }
        .admin-login-text { letter-spacing: 0.04em; font-weight: 500; line-height: 1; }
        @media (max-width: 640px) {
            .admin-login-btn { padding: 3px 6px; }
        }
    </style>
</head>
<body class="bg-white min-h-screen relative overflow-x-hidden flex flex-col pt-20">

    <!-- Top Navigation Bar (Fixed) -->
    <nav class="fixed top-0 w-full z-50 flex items-center justify-between gap-4 px-4 sm:px-8 py-4 bg-[#0f172a] shadow-lg border-b border-white/5 transition-all duration-500" id="top-nav">
        <a href="{{ route('portal.index') }}" class="flex items-center space-x-4 cursor-pointer group min-w-0">
            <div class="relative text-[#cc0000] drop-shadow-2xl transition-transform duration-500 group-hover:scale-110">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                <svg class="w-3 h-3 text-[#00ff00] absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="2"/></svg>
            </div>
            <div class="flex flex-col">
                <span class="text-white text-[15px] font-bold tracking-[0.15em] drop-shadow-lg uppercase font-oswald">SHE LCM</span>
                <span class="text-white/70 text-[9px] font-medium tracking-[0.2em] -mt-1 uppercase">PT LAZ COAL MANDIRI</span>
            </div>
        </a>
        <div class="flex items-center shrink-0">
            <a href="{{ route('admin.login.php') }}"
               class="admin-login-btn inline-flex items-center gap-2 rounded-md px-2.5 py-1 text-[12px] font-medium focus:outline-none"
               aria-label="Masuk Admin"
               title="Masuk ke panel administrator">
                <span class="admin-login-icon" aria-hidden="true">
                    <svg class="admin-login-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 0A2.25 2.25 0 0 0 4.5 12.75v5.25A2.25 2.25 0 0 0 6.75 20.25h10.5A2.25 2.25 0 0 0 19.5 18v-5.25A2.25 2.25 0 0 0 17.25 12.75h-10.5Z" />
                    </svg>
                </span>
                <span class="admin-login-text">Admin</span>
            </a>
        </div>
    </nav>

    <!-- Signature Red Block -->
    <div class="red-block"></div>

    <!-- Header Section -->
    <header class="relative z-10 w-full page-container pt-16 pb-12 flex flex-col md:flex-row justify-between items-center md:items-end gap-10">
        <div class="flex items-center gap-8">
            <img src="{{ asset('assets/branding/remote/lcm-logo.png') }}" alt="Logo LCM" class="h-20 w-auto drop-shadow-md">
            <div class="flex flex-col border-l-[3px] border-gray-100 pl-8 py-1">
                <h1 class="text-[32px] lg:text-[42px] font-oswald font-bold text-gray-900 leading-none tracking-tight uppercase">SIMPER & <span class="text-red-700">MINE PERMIT</span></h1>
                <p class="text-[12px] font-bold text-gray-400 tracking-[0.4em] uppercase mt-3">PORTAL ADMINISTRASI TAMBANG</p>
            </div>
        </div>
    </header>

    <!-- Content Grid -->
    <main class="relative z-10 flex-grow w-full page-container py-12 lg:py-24">
        
        @php
            $cat3All = $formsByCategory[3] ?? [];
            $cat4All = $formsByCategory[4] ?? [];

            // Grouping Logic
            $col1Forms = array_values(array_filter($cat4All, fn($f) => $f['purpose'] === 'pengajuan'));
            $form11 = array_filter($cat3All, fn($f) => (int)($f['id'] ?? 0) === 11);
            if (!empty($form11)) $col1Forms = array_merge($col1Forms, $form11);
            usort($col1Forms, function($a, $b) {
                $getOrder = fn($t) => match(true) {
                    str_contains(strtolower($t), 'new hire') || (str_contains(strtolower($t), 'pengajuan') && str_contains(strtolower($t), 'simper') && !str_contains(strtolower($t), 'rusak')) => 0,
                    str_contains(strtolower($t), 'perp') => 1,
                    default => 2
                };
                return $getOrder($a['title']) <=> $getOrder($b['title']);
            });

            $col2Forms = array_values(array_filter($cat3All, function($f) {
                return $f['purpose'] === 'pengajuan' && (int)($f['id'] ?? 0) !== 11 && (int)($f['id'] ?? 0) !== 10;
            }));
            usort($col2Forms, function($a, $b) {
                $getOrder = fn($t) => match(true) {
                    str_contains(strtolower($t), 'new hire') => 0,
                    str_contains(strtolower($t), 'perp') => 1,
                    default => 2
                };
                return $getOrder($a['title']) <=> $getOrder($b['title']);
            });

            $col3Forms = [];
            foreach (array_merge($cat4All, $cat3All) as $f) {
                if ($f['purpose'] === 'monitoring' && (int)($f['id'] ?? 0) !== 10) $col3Forms[] = $f;
            }
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-y-20">
            
            <!-- Column 1: Internal -->
            <div class="col-divider px-0 lg:px-12 text-center flex flex-col items-center animate-fade-in-up" style="animation-delay: 100ms;">
                <div class="w-16 h-1.5 bg-red-700 mb-8 rounded-full shadow-lg"></div>
                <h2 class="text-[24px] font-oswald font-bold text-gray-900 leading-tight mb-5 uppercase tracking-wider">
                    PENGAJUAN<br>INTERNAL LCM
                </h2>
                <p class="text-[14px] text-gray-500 mb-12 px-6 leading-relaxed font-medium">
                    Formulir pengajuan khusus untuk karyawan internal PT Laz Coal Mandiri Site Kintap.
                </p>
                <div class="flex flex-col gap-5 w-full">
                    @forelse($col1Forms as $form)
                        <a href="{{ $form['form_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-outlined group">
                            <span>{{ $form['title'] }}</span>
                            <svg class="w-4 h-4 opacity-30 group-hover:opacity-100 transition-all group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    @empty
                        <span class="text-sm text-gray-400 italic">Data belum tersedia.</span>
                    @endforelse
                </div>
            </div>

            <!-- Column 2: Subcontractor -->
            <div class="col-divider px-0 lg:px-12 text-center flex flex-col items-center animate-fade-in-up" style="animation-delay: 250ms;">
                <div class="w-16 h-1.5 bg-gray-200 mb-8 rounded-full"></div>
                <h2 class="text-[24px] font-oswald font-bold text-gray-900 leading-tight mb-5 uppercase tracking-wider">
                    PENGAJUAN<br>SUBKONTRAKTOR
                </h2>
                <p class="text-[14px] text-gray-500 mb-12 px-6 leading-relaxed font-medium">
                    Formulir pengajuan untuk seluruh mitra kerja dan subkontraktor di lingkungan PT LCM.
                </p>
                <div class="flex flex-col gap-5 w-full">
                    @forelse($col2Forms as $form)
                        <a href="{{ $form['form_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-outlined group">
                            <span>{{ $form['title'] }}</span>
                            <svg class="w-4 h-4 opacity-30 group-hover:opacity-100 transition-all group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    @empty
                        <span class="text-sm text-gray-400 italic">Data belum tersedia.</span>
                    @endforelse
                </div>
            </div>

            <!-- Column 3: Monitoring -->
            <div class="px-0 lg:px-12 text-center flex flex-col items-center animate-fade-in-up" style="animation-delay: 400ms;">
                <div class="w-16 h-1.5 bg-gray-200 mb-8 rounded-full"></div>
                <h2 class="text-[24px] font-oswald font-bold text-gray-900 leading-tight mb-5 uppercase tracking-wider">
                    MONITORING<br>PENGAJUAN
                </h2>
                <p class="text-[14px] text-gray-500 mb-12 px-6 leading-relaxed font-medium">
                    Pantau status pengajuan SIMPER & Mine Permit Anda secara real-time.
                </p>
                <div class="flex flex-col gap-5 w-full">
                    @forelse($col3Forms as $form)
                        <a href="{{ $form['form_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-outlined group">
                            <span>{{ $form['title'] }}</span>
                            <svg class="w-4 h-4 opacity-30 group-hover:opacity-100 transition-all group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    @empty
                        <span class="text-sm text-gray-400 italic">Data belum tersedia.</span>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- System Transition Highlight -->
        <div class="mt-24 p-12 bg-gray-50 border border-gray-100 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-10 animate-fade-in-up" style="animation-delay: 500ms;">
            <div class="flex-grow">
                <div class="inline-block px-3 py-1 bg-red-700 text-white text-[9px] font-black tracking-widest uppercase rounded mb-4">New Digital System</div>
                <h3 class="text-2xl font-oswald font-bold text-gray-900 mb-2 uppercase">Pengajuan SIMPER & Mine Permit Digital</h3>
                <p class="text-sm text-gray-500 leading-relaxed max-w-xl">
                    Kami sedang melakukan transisi dari Google Forms ke sistem portal terintegrasi. Untuk pengajuan baru melalui jalur digital atau jika Anda telah memiliki akun staff, silakan gunakan tautan di bawah ini.
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.submissions.index') }}" class="btn-outlined !bg-red-700 !text-white !border-red-700 hover:!bg-red-800">
                    <span>Akses Portal Monitoring</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Enterprise Footer -->
    <footer class="w-full relative bg-[#111] border-t-8 border-red-700 shadow-[0_-20px_50px_rgba(0,0,0,0.5)] mt-auto pt-20 pb-12 z-20">
        <div class="page-container">
            <div class="flex flex-col md:flex-row justify-between items-start gap-16 mb-20">
                <!-- Branding -->
                <div class="w-full md:w-1/3">
                    <div class="flex items-center space-x-5 mb-8">
                        <div class="relative w-[54px] h-[62px] bg-red-700 rounded-t-[45%] rounded-b-md border-b-4 border-red-900 flex flex-col items-center justify-center shadow-2xl">
                            <svg class="w-[18px] h-[18px] text-[#00ff00] absolute top-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                            <span class="text-white text-3xl font-black italic mt-1 drop-shadow-2xl font-oswald">S</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-white text-2xl font-black tracking-[0.2em] drop-shadow-md leading-none mb-1 font-oswald">S-GUARDS</h3>
                            <p class="text-white/40 text-[10px] leading-tight font-bold uppercase tracking-[0.3em]">SHE DEPARTMENT SITE KINTAP</p>
                        </div>
                    </div>
                    <p class="text-white/40 text-[13px] leading-relaxed font-light text-justify">
                        Sistem manajemen keselamatan pertambangan terintegrasi PT LAZ COAL MANDIRI. Berkomitmen tinggi untuk menciptakan lingkungan kerja yang aman, sehat, dan produktif bagi seluruh insan pertambangan.
                    </p>
                </div>

                <!-- Contact/Dept -->
                <div class="w-full md:w-1/4">
                    <h4 class="text-white text-[12px] font-black tracking-[0.4em] uppercase mb-10 pb-4 border-b border-white/10 font-oswald">Department Info</h4>
                    <ul class="space-y-6">
                        <li class="flex items-start space-x-4">
                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="text-white/60 text-[12px] font-medium leading-relaxed uppercase tracking-wider">SHE Dept Office<br/>PT LAZ COAL MANDIRI<br/>Site Kintap, Kalimantan Selatan</span>
                        </li>
                    </ul>
                </div>

                <!-- Tagline -->
                <div class="w-full md:w-1/4 text-right">
                    <div class="space-y-4">
                        <div class="text-[50px] font-black italic text-white/5 leading-none select-none font-oswald">VISIONARY</div>
                        <div class="text-red-700 font-black text-2xl tracking-[0.3em] uppercase drop-shadow-lg font-oswald">THINK SAFETY</div>
                        <div class="text-white font-black text-2xl tracking-[0.3em] uppercase font-oswald">ACT SAFELY</div>
                        <div class="text-white/40 font-black text-xl tracking-[0.3em] uppercase font-oswald">GET PRODUCTIVITY</div>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-white/20 text-[10px] font-bold tracking-[0.3em] uppercase">
                    &copy; {{ date('Y') }} PT LAZ COAL MANDIRI. All Rights Reserved.
                </p>
                <div class="flex items-center space-x-8">
                    <span class="text-white/20 text-[10px] font-bold tracking-[0.3em] uppercase">Security Protocol v4.6</span>
                    <span class="text-white/20 text-[10px] font-bold tracking-[0.3em] uppercase">ISO 45001 Certified</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('top-nav');
            if (window.scrollY > 50) {
                nav.classList.add('bg-black', 'py-3', 'shadow-2xl');
                nav.classList.remove('py-4', 'bg-[#0f172a]');
            } else {
                nav.classList.remove('bg-black', 'py-3', 'shadow-2xl');
                nav.classList.add('py-4', 'bg-[#0f172a]');
            }
        });
    </script>
</body>
</html>

