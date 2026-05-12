<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal formulir keselamatan Laz Coal Mandiri untuk akses pengajuan dan monitoring operasional.">
    <meta name="theme-color" content="#5f1c21">
    <title>Portal Form SHE APPS LCM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans text-gray-800 bg-gray-50 flex flex-col min-h-screen">
    @php
        $isAdminAuthenticated = (bool) ($isAdminAuthenticated ?? false);
        $user = App\Support\Legacy\LegacyAuth::user();
        $userRole = (string) ($user['role'] ?? '');
    @endphp

    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-4">
                    <a href="{{ route('portal.index') }}" class="flex items-center gap-3 group">
                        <img class="h-12 w-12 object-contain transition-transform group-hover:scale-105" src="{{ asset('assets/branding/remote/lcm-logo.png') }}" alt="Logo Laz Coal Mandiri">
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold tracking-wider text-red-700 uppercase">Laz Coal Mandiri</span>
                            <span class="text-xl font-bold text-gray-900 tracking-tight">SHE <span class="text-red-600">APPS</span></span>
                        </div>
                    </a>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('portal.index') }}" class="text-gray-500 hover:text-red-600 font-medium transition-colors">Halaman Awal</a>
                    <a href="https://www.lazcoalmandiri.co.id/" target="_blank" class="text-gray-500 hover:text-red-600 font-medium transition-colors">Website LCM</a>
                    @if (in_array($userRole, ['admin','she']))
                        <a href="{{ route('admin.monitoring.php') }}" class="text-gray-500 hover:text-red-600 font-medium transition-colors">Monitoring Admin</a>
                    @endif
                    <a href="{{ route('admin.login') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg border border-gray-200 hover:bg-gray-200 transition-all">Konsol Admin</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Form Section -->
    <section class="pt-20 pb-32 relative overflow-hidden isolate bg-[#0f172a]">
        <div class="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-overlay" style="background-image: url('{{ asset('assets/branding/remote/hero-operation.jpg') }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-[#0f172a] via-[#0f172a]/95 to-[#0f172a]/90"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <span class="inline-block px-4 py-1.5 rounded-full text-sm font-bold mb-6 bg-red-600 text-white border border-red-500/30">
                        LCM-SHE Safety Center
                    </span>
                    <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight mb-6">
                        Portal Formulir <br/>
                        <span class="text-red-500">Keselamatan Operasional</span>
                    </h1>
                    <p class="text-gray-200 text-lg leading-relaxed max-w-2xl mx-auto lg:mx-0 mb-10">
                        Pilih kategori form berdasarkan kebutuhan operasional, lalu lanjutkan proses pengajuan atau monitoring dengan alur yang konsisten, cepat, dan terkontrol.
                    </p>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 p-6 rounded-2xl mb-10 bg-[#1e293b] border border-white/10 shadow-2xl">
                        <div class="flex flex-col gap-1">
                            <span class="text-3xl font-extrabold text-white">{{ count($categories ?? []) }}</span>
                            <span class="text-gray-300 text-xs uppercase tracking-wider">Kategori Aktif</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-3xl font-extrabold text-white">{{ $totalForms ?? 0 }}</span>
                            <span class="text-gray-300 text-xs uppercase tracking-wider">Form Aktif</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-3xl font-extrabold text-white">{{ $formsExpiringSoon ?? 0 }}</span>
                            <span class="text-gray-300 text-xs uppercase tracking-wider">Expired &lt;= 7 Hari</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="#kategori-form" class="w-full sm:w-auto px-8 py-4 rounded-xl font-bold bg-white text-gray-900 hover:bg-gray-100 transition-all shadow-lg text-center">Mulai Akses Form</a>
                        <a href="#alur-pengajuan" class="w-full sm:w-auto px-8 py-4 rounded-xl font-bold text-white bg-[#1e293b] hover:bg-[#334155] border border-white/10 transition-all shadow-lg text-center">Lihat Alur Layanan</a>
                    </div>
                </div>

                <div class="lg:ml-auto w-full max-w-md">
                    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <img src="{{ asset('assets/branding/remote/hero-operation.jpg') }}" alt="Tim operasional Laz Coal Mandiri" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-lg font-extrabold text-gray-900 mb-3">Standar Akses Form LCM</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm text-gray-600">Tautan formulir aktif langsung bersumber dari konfigurasi terverifikasi.</p>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm text-gray-600">Proses pengajuan dan monitoring dipisah jelas untuk menjaga akurasi data.</p>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm text-gray-600">Dokumen wajib disusun untuk mempercepat validasi keselamatan kerja.</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Nav -->
    <nav class="sticky top-20 z-40 bg-gray-50 border-b border-gray-200 shadow-sm" aria-label="Navigasi cepat portal" data-section-nav>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex overflow-x-auto whitespace-nowrap gap-8 hide-scrollbar">
            <a class="py-4 text-sm font-semibold text-red-600 border-b-2 border-red-600" href="#kategori-form">Kategori Formulir</a>
            <a class="py-4 text-sm font-medium text-gray-600 hover:text-gray-900" href="#flow-proses">Flow Proses</a>
            <a class="py-4 text-sm font-medium text-gray-600 hover:text-gray-900" href="{{ route('portal.documents.show', ['code' => 'ktp-ohs-102-mine-permit-simper']) }}">Dokumen KTP-OHS-102</a>
            <a class="py-4 text-sm font-medium text-gray-600 hover:text-gray-900" href="#alur-pengajuan">Alur Layanan</a>
            <a class="py-4 text-sm font-medium text-gray-600 hover:text-gray-900" href="#aturan-internal">Ketentuan Internal</a>
            <a class="py-4 text-sm font-medium text-gray-600 hover:text-gray-900" href="#dokumen-wajib">Dokumen Wajib</a>
            <a class="py-4 text-sm font-medium text-gray-600 hover:text-gray-900" href="#bucket-sapkon">Bucket SAPKON</a>
        </div>
    </nav>

    <main id="main-content" class="flex-grow bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-20">
            
            <!-- Flow Proses Pengajuan -->
            @if(!empty($flowDocuments))
            <section id="flow-proses" class="scroll-mt-32">
                <div class="max-w-3xl mb-8">
                    <p class="text-red-600 font-bold tracking-wider uppercase text-sm mb-2">Panduan Alur</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Flow Proses Pengajuan</h2>
                    <p class="mt-4 text-gray-600">Unduh diagram alur proses pengajuan SIMPER dan Mine Permit sebagai panduan tahapan yang harus dilalui oleh seluruh personel.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($flowDocuments as $flowDoc)
                    <a href="{{ route('portal.documents.show', ['code' => $flowDoc['code']]) }}"
                       class="group flex flex-col bg-white border-2 border-gray-100 hover:border-red-200 rounded-2xl overflow-hidden hover:shadow-lg transition-all">
                        <div class="bg-gradient-to-br from-red-700 to-red-900 p-6 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                            </div>
                            <div>
                                <p class="text-white/60 text-[10px] font-bold tracking-widest uppercase">Dokumen Alur</p>
                                <p class="text-white font-bold text-sm leading-tight">{{ $flowDoc['revision_label'] ?? 'Rev1' }}</p>
                            </div>
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="font-bold text-gray-900 mb-2 leading-snug group-hover:text-red-700 transition-colors">{{ $flowDoc['title'] }}</h3>
                            <p class="text-sm text-gray-500 mb-4 flex-grow">{{ $flowDoc['description'] }}</p>
                            <div class="flex items-center justify-between mt-auto">
                                <span class="text-xs text-gray-400">{{ number_format(($flowDoc['file_size'] ?? 0) / 1024, 0) }} KB</span>
                                <span class="flex items-center gap-1.5 text-xs font-bold text-red-600 group-hover:text-red-700">
                                    Lihat &amp; Unduh
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- Kategori Formulir -->
            <section id="kategori-form" class="scroll-mt-32">
                <div class="max-w-3xl mb-10">
                    <p class="text-red-600 font-bold tracking-wider uppercase text-sm mb-2">Akses Formulir</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Daftar Formulir per Kategori</h2>
                    <p class="mt-4 text-gray-600">Pilih kategori, kemudian buka formulir pengajuan atau monitoring sesuai proses operasional yang berjalan.</p>
                    @if (!$isAdminAuthenticated)
                        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            Akses menu <strong>Monitoring</strong> hanya untuk admin login.
                            <a href="{{ route('admin.login') }}" class="font-semibold underline underline-offset-2">Masuk sebagai admin</a>
                            untuk melihat dan membuka tautan monitoring.
                        </div>
                    @endif
                </div>

                <!-- Search -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-10">
                    <label for="portal-category-search" class="block text-sm font-medium text-gray-700 mb-2">Quick Finder Formulir</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35"/>
                                <circle cx="11" cy="11" r="6" stroke-width="2"></circle>
                            </svg>
                        </div>
                        <input
                            id="portal-category-search"
                            type="search"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm transition-colors"
                            placeholder="Cari kategori, judul formulir, atau kata kunci proses..."
                            aria-label="Cari kategori formulir"
                            data-portal-filter-input
                            data-portal-filter-target="#portalCategoryGrid .category-card"
                        >
                    </div>
                    <div class="mt-3 flex justify-between items-center">
                        <p class="text-xs text-gray-500">Tips: tekan tombol <kbd class="px-1.5 py-0.5 bg-gray-100 rounded border border-gray-200 font-mono">/</kbd> dari keyboard untuk langsung fokus ke pencarian.</p>
                        <button type="button" class="text-xs font-bold text-red-600 hover:text-red-500" data-portal-filter-reset>Bersihkan Pencarian</button>
                    </div>
                    <p class="mt-2 text-sm font-medium text-gray-700" data-portal-filter-count aria-live="polite">0 kategori ditampilkan</p>
                </div>

                <!-- Category Grid -->
                <div id="portalCategoryGrid" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach ($categories ?? [] as $category)
                        <article class="bg-white border-2 border-gray-100 rounded-2xl overflow-hidden hover:shadow-md transition-all category-card flex flex-col" data-ui-reveal>
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">{{ $category['name'] ?? '' }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">Status: Aktif</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                </div>
                                <p class="text-sm text-gray-600">{!! nl2br(e((string) ($category['description'] ?? ''))) !!}</p>
                            </div>

                            @php
                                $items = $formsByCategory[(int) ($category['id'] ?? 0)] ?? [];
                            @endphp

                            <div class="p-6 bg-gray-50/50 flex-grow">
                                @if (!$items)
                                    <div class="text-center py-6">
                                        <div class="w-10 h-10 mx-auto bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-3">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-sm text-gray-500">Belum tersedia formulir aktif pada kategori ini.</p>
                                    </div>
                                @else
                                    @php
                                        $submissionForms = [];
                                        $monitoringForms = [];
                                        foreach ($items as $form) {
                                            if ((string) ($form['purpose'] ?? '') === 'monitoring') {
                                                $monitoringForms[] = $form;
                                            } else {
                                                $submissionForms[] = $form;
                                            }
                                        }
                                    @endphp

                                    <div class="space-y-6">
                                        @if ($submissionForms)
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Pengajuan</p>
                                                <div class="grid gap-3">
                                                    @foreach ($submissionForms as $form)
                                                        <a href="{{ $form['form_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg hover:border-red-300 hover:shadow-sm transition-all group">
                                                            <span class="px-2 py-1 text-[0.65rem] font-bold bg-blue-50 text-blue-600 rounded-md">PENGAJUAN</span>
                                                            <span class="text-sm font-medium text-gray-700 group-hover:text-red-600">{{ $form['title'] ?? '' }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if (in_array($userRole, ['admin','she']) && $monitoringForms)
                                            <div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Monitoring</p>
                                                <div class="grid gap-3">
                                                    @foreach ($monitoringForms as $form)
                                                        @php
                                                            $monitoringFormId = (int) ($form['id'] ?? 0);
                                                            $monitoringOpenUrl = $monitoringFormId > 0
                                                                ? route('admin.monitoring.forms.open', ['id' => $monitoringFormId])
                                                                : route('admin.monitoring.php');
                                                        @endphp
                                                        <a href="{{ $monitoringOpenUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg hover:border-red-300 hover:shadow-sm transition-all group">
                                                            <span class="px-2 py-1 text-[0.65rem] font-bold bg-amber-50 text-amber-600 rounded-md">MONITORING</span>
                                                            <span class="text-sm font-medium text-gray-700 group-hover:text-red-600">{{ $form['title'] ?? '' }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if (!in_array($userRole, ['admin','she']))
                                            <div class="rounded-lg border border-dashed border-gray-300 bg-white px-4 py-3">
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Monitoring</p>
                                                <p class="text-sm text-gray-600">Monitoring hanya tersedia untuk role SHE atau Admin. Silakan masuk sebagai akun yang memiliki akses.</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <p class="hidden text-center text-sm text-gray-500 py-8" data-portal-filter-empty>Tidak ada kategori/formulir yang cocok dengan pencarian Anda.</p>

                <div class="mt-12 bg-blue-50/50 border border-blue-100 p-5 rounded-xl flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-blue-900 leading-relaxed">
                        <strong>Catatan: </strong>Seluruh tautan formulir pada halaman ini bersumber dari data aktif di panel administrator. Jika terdapat pembaruan proses, tim administrator cukup memperbarui data tanpa perubahan kode aplikasi.
                    </p>
                </div>
            </section>

            <!-- Alur -->
            <section id="alur-pengajuan" class="scroll-mt-32">
                <div class="max-w-3xl mb-10">
                    <p class="text-red-600 font-bold tracking-wider uppercase text-sm mb-2">Alur Layanan</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Alur Layanan Singkat</h2>
                    <p class="mt-4 text-gray-600">Standar alur yang konsisten membantu tim memproses permohonan lebih cepat dengan koreksi minimal.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <article class="bg-white p-8 rounded-2xl shadow-sm flex flex-col relative" data-ui-reveal>
                        <span class="absolute top-6 right-6 text-6xl font-black text-gray-100 opacity-50">01</span>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 relative z-10">Pilih Kategori</h3>
                        <p class="text-gray-600 relative z-10">Pilih kategori sesuai kebutuhan operasional: IKK, Visitor/Temporary, atau SIMPER dan Mine Permit.</p>
                    </article>
                    <article class="bg-blue-700 text-white p-8 rounded-2xl shadow-md flex flex-col relative transform scale-105 z-10" data-ui-reveal>
                        <span class="absolute top-6 right-6 text-6xl font-black text-blue-600 opacity-50">02</span>
                        <h3 class="text-xl font-bold mb-4 relative z-10">Buka Formulir</h3>
                        <p class="text-blue-100 relative z-10">Klik tautan pengajuan atau monitoring. Setiap perubahan tautan dikelola administrator secara real-time.</p>
                    </article>
                    <article class="bg-white p-8 rounded-2xl shadow-sm flex flex-col relative" data-ui-reveal>
                        <span class="absolute top-6 right-6 text-6xl font-black text-gray-100 opacity-50">03</span>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 relative z-10">Verifikasi, Dokumen</h3>
                        <p class="text-gray-600 relative z-10">Pastikan dokumen wajib lengkap agar proses verifikasi keselamatan berlangsung lebih cepat dan konsisten.</p>
                    </article>
                </div>
            </section>

            <!-- Ketentuan Internal -->
            <section id="aturan-internal" class="scroll-mt-32 mt-40 inset-0">
                <div class="max-w-3xl mb-10">
                    <p class="text-red-600 font-bold tracking-wider uppercase text-sm mb-2">Tata Kelola Internal</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Ketentuan Pengajuan Internal</h2>
                    <p class="mt-4 text-gray-600">Segmentasi perusahaan internal membantu mengurangi kesalahan input dan mempercepat validasi.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($internalGroupMap ?? [] as $groupName => $rows)
                        <article class="bg-white p-7 rounded-2xl shadow-sm border border-gray-100" data-ui-reveal>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-4">{{ (string) $groupName }}</h3>
                            <ul class="space-y-3">
                                @foreach ($rows as $row)
                                    @php
                                    $companyName = (string) ($row['company_name'] ?? '');
                                    $isManualInputAllowed = ((int) ($row['is_manual_input_allowed'] ?? 0)) === 1;
                                    @endphp
                                    <li class="flex items-center gap-3 text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <span>
                                            {{ $companyName }}
                                            @if ($isManualInputAllowed)
                                                <span class="text-gray-400 font-normal inline-flex items-center gap-1 ml-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 6H3m8 0h6m-6 4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    manual
                                                </span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    @endforeach
                </div>
            </section>

            <!-- Dokumen Wajib + SAPKON -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mt-24 pb-24">
                <!-- Dokumen Wajib -->
                <section id="dokumen-wajib" class="scroll-mt-32">
                    <div class="mb-8">
                        <p class="text-red-600 font-bold tracking-wider uppercase text-sm mb-2">Checklist Keselamatan</p>
                        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Dokumen Wajib</h2>
                        <p class="mt-4 text-gray-600">Checklist dokumen dibuat konsisten agar proses review keselamatan lebih akurat.</p>
                    </div>
                    <div class="space-y-6">
                        <article class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100" data-ui-reveal>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Persyaratan SIMPER
                            </h3>
                            <ul class="space-y-2">
                                @foreach ($requiredDocsMap['simper'] ?? [] as $doc)
                                    <li class="flex items-start gap-3 text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>
                                            {{ (string) ($doc['doc_name'] ?? '') }}
                                            @if (((int) ($doc['is_conditional'] ?? 0)) === 1)
                                                <span class="text-gray-400 italic ml-1">({{ (string) ($doc['condition_notes'] ?? '') }})</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                        <article class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100" data-ui-reveal>
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Persyaratan Mine Permit
                            </h3>
                            <ul class="space-y-2">
                                @foreach ($requiredDocsMap['mine_permit'] ?? [] as $doc)
                                    <li class="flex items-start gap-3 text-sm text-gray-600">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>
                                            {{ (string) ($doc['doc_name'] ?? '') }}
                                            @if (((int) ($doc['is_conditional'] ?? 0)) === 1)
                                                <span class="text-gray-400 italic ml-1">({{ (string) ($doc['condition_notes'] ?? '') }})</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    </div>
                </section>

                <!-- Bucket SAPKON -->
                <section id="bucket-sapkon" class="scroll-mt-32">
                    <div class="mb-8">
                        <p class="text-red-600 font-bold tracking-wider uppercase text-sm mb-2">Integrasi DATA</p>
                        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Bucket SAPKON</h2>
                        <p class="mt-4 text-gray-600">Klasifikasi bucket SAPKON yang digunakan untuk memetakan hubungan kerja subkontraktor dan vendor.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($sapkonBuckets ?? [] as $bucket)
                            <a
                                href="{{ (string) ($bucket['form_url'] ?? '#') }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="bg-gray-100/50 p-5 rounded-xl border border-gray-200 flex items-center gap-3 hover:bg-gray-100 hover:border-red-200 transition-colors"
                                data-ui-reveal
                            >
                                <div class="px-2 py-1 rounded-md border border-gray-300 bg-white text-gray-500 font-bold text-xs shadow-sm">
                                    {{ strtoupper((string) ($bucket['form_type'] ?? '-')) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ (string) ($bucket['sapkon_name'] ?? '-') }}</p>
                                    <p class="text-xs text-gray-500">Kode: {{ (string) ($bucket['sapkon_code'] ?? '-') }}</p>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada data bucket SAPKON yang aktif.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div><!-- end max-w -->
    </main>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-300 py-16 border-t border-gray-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-12 gap-12">
            <div class="md:col-span-5 space-y-6">
                <div class="flex items-center gap-4 mb-6">
                    <img class="h-10 w-10 filter brightness-0 invert opacity-90" src="{{ asset('assets/branding/remote/lcm-logo.png') }}" alt="Logo Laz Coal Mandiri">
                    <div class="flex flex-col">
                        <span class="text-white font-bold text-xl tracking-wide">SHE APPS <span class="text-red-500">LCM</span></span>
                        <span class="text-xs text-gray-500 uppercase tracking-widest font-bold">Site Kintap Project</span>
                    </div>
                </div>
                <p class="text-sm text-gray-400 leading-loose">
                    Sistem Tata Kelola Keselamatan Resmi PT. Laz Coal Mandiri, Site Kintap. Merawat konsistensi proses demi menciptakan ruang lingkup kerja pertambangan yang aman, sehat, dan berwawasan lingkungan.
                </p>
                <div class="flex items-center gap-4 pt-2">
                    <a href="https://www.lazcoalmandiri.co.id/" target="_blank" class="px-5 py-2.5 rounded-lg bg-gray-900 border border-gray-800 hover:border-gray-600 hover:bg-gray-800 transition-all text-sm font-medium text-white">
                        Kunjungi Website Resmi
                    </a>
                </div>
            </div>
            
            <div class="md:col-span-3 md:col-start-7 text-sm space-y-4">
                <h4 class="text-white font-bold uppercase tracking-wider mb-6">Tautan Cepat</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('portal.index') }}" class="text-gray-400 hover:text-red-400 transition-colors">Beranda</a></li>
                    <li><a href="{{ route('portal.forms') }}" class="text-gray-400 hover:text-red-400 transition-colors">Dokumen & Form SHE</a></li>
                    <li><a href="{{ route('admin.login') }}" rel="nofollow" class="text-gray-400 hover:text-red-400 transition-colors">Login Admin</a></li>
                </ul>
            </div>
            
            <div class="md:col-span-3 text-sm space-y-4">
                <h4 class="text-white font-bold uppercase tracking-wider mb-6">Kontak Operasional</h4>
                <address class="not-italic text-gray-400 space-y-3 leading-relaxed">
                    <p class="font-medium text-gray-300">PT. Laz Coal Mandiri (Site Kintap)</p>
                    <p>Kalimantan Selatan, Indonesia</p>
                    <p>Fokus Operasional: Pertambangan Batubara Terintegrasi.</p>
                </address>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-8 border-t border-gray-900 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-gray-500 font-medium tracking-wide">
                &copy; {{ date('Y') }} PT. Laz Coal Mandiri (LCM). All Rights Reserved.
            </p>
            <p class="text-xs text-gray-600 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                Sistem Aman & Terasuransi
            </p>
        </div>
    </footer>

</body>
</html>