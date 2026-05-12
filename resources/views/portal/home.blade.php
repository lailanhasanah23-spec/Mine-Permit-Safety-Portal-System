<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SHE LCM PT LAZ COAL MANDIRI: Portal keselamatan pertambangan terintegrasi untuk Site Kintap.">
    <meta name="theme-color" content="#b91c1c">
    <title>SHE LCM | Portal Keselamatan Pertambangan Terintegrasi</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="antialiased font-sans text-gray-800 bg-white flex flex-col min-h-screen selection:bg-[#005a73] selection:text-white overflow-x-hidden">
    
    <!-- Top Navigation Bar (Fixed) -->
    <nav class="fixed top-0 w-full z-50 flex items-center justify-between gap-4 px-4 sm:px-8 py-4 bg-[#0f172a] shadow-lg border-b border-white/5 transition-all duration-500" id="top-nav">
        <a href="javascript:void(0)" onclick="showView('portal')" class="flex items-center space-x-4 cursor-pointer group min-w-0">
            <div class="relative text-[#cc0000] drop-shadow-2xl transition-transform duration-500 group-hover:scale-110">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                <svg class="w-3 h-3 text-[#00ff00] absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="2"/></svg>
            </div>
            <div class="flex flex-col">
                <span class="text-white text-[15px] font-bold tracking-[0.15em] drop-shadow-lg uppercase">SHE LCM</span>
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

    <!-- VIEW: MAIN PORTAL -->
    <div id="view-portal" class="flex flex-col min-h-screen transition-opacity duration-700 opacity-100">
        <!-- Hero Section -->
        <section class="relative w-full h-[700px] flex flex-col items-center justify-start pt-44 overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-transform duration-[10000ms] ease-linear scale-100" style="background-image: url('{{ asset('assets/branding/remote/hero-operation.jpg') }}')" id="hero-bg"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#0f172a] via-[#0f172a]/95 to-[#f0f2f5]"></div>
            
            <div class="relative z-10 text-center text-white flex flex-col items-center px-4">
                <div class="mb-4 opacity-0 animate-fade-in-up" style="animation-delay: 200ms; animation-fill-mode: forwards;">
                    <span class="px-4 py-1.5 bg-red-600 border border-red-700 rounded-full text-[10px] font-bold tracking-[0.3em] uppercase">Official Safety Portal</span>
                </div>
                <h1 class="text-[80px] md:text-[110px] font-oswald font-bold tracking-tight mb-0 drop-shadow-2xl leading-none uppercase">SHE LCM</h1>
                <h2 class="text-[20px] md:text-[28px] font-oswald font-light tracking-[0.4em] mb-2 drop-shadow-xl uppercase text-white/90">PORTAL KESELAMATAN TERINTEGRASI</h2>
                <div class="w-16 h-1 bg-red-600 my-6 rounded-full shadow-lg"></div>
                <h3 class="text-[24px] md:text-[32px] font-oswald font-medium tracking-[0.2em] drop-shadow-md uppercase opacity-80">PT LAZ COAL MANDIRI</h3>
            </div>
        </section>

        <!-- Carousel & Indicator Section -->
        <section class="relative w-full flex flex-col items-center pb-16">
            <div class="relative z-20 w-[90%] max-w-[1100px] mx-auto mt-[-260px] aspect-[21/9] bg-[#0a0f14] overflow-hidden rounded-xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] group" id="carousel">
                <img id="carouselImage" src="{{ asset('assets/branding/remote/hero-operation.jpg') }}" alt="Slide Pertambangan" class="w-full h-full object-cover transition-all duration-1000 ease-in-out group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                <button onclick="prevSlide()" class="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all opacity-0 group-hover:opacity-100 border border-white/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button onclick="nextSlide()" class="absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-all opacity-0 group-hover:opacity-100 border border-white/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>

            <div class="relative z-10 flex flex-col items-center mt-10 w-full">
                <div class="flex justify-center space-x-3" id="dotsContainer"></div>
            </div>
        </section>

        <!-- About Section -->
        <section class="bg-[#f0f2f5] py-24 border-t border-gray-200">
            <div class="max-w-[1100px] mx-auto px-8">
                <div class="flex flex-col md:flex-row items-start gap-16">
                    <!-- Left Column -->
                    <div class="w-full md:w-1/2 flex flex-col">
                        <div class="flex items-center space-x-4 mb-8">
                            <span class="w-12 h-[2px] bg-[#005a73]"></span>
                            <h2 class="text-[13px] font-bold tracking-[0.3em] text-[#005a73] uppercase">Informasi Keselamatan</h2>
                        </div>
                        <h3 class="text-3xl font-oswald font-bold text-gray-900 mb-6 uppercase tracking-tight leading-tight">
                            Pusat Pengetahuan & <br/><span class="text-[#005a73]">Budaya Keselamatan</span>
                        </h3>
                        <p class="text-gray-600 text-justify leading-loose text-[15px] mb-8 font-light">
                            Portal informasi Keselamatan Pertambangan berfungsi sebagai pusat pembelajaran dan informasi komprehensif mengenai keselamatan pertambangan, mencakup studi kasus (lesson learned), regulasi terkini, dan sumber daya lainnya untuk mendukung praktik kerja yang aman di Site Kintap.
                        </p>
                            <a href="javascript:void(0)" onclick="showView('keselamatan')" class="group flex items-center space-x-3 text-[13px] font-bold text-[#005a73] tracking-widest uppercase">
                                <span>Jelajahi Informasi</span>
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                    </div>

                    <!-- Right Column -->
                    <div class="w-full md:w-1/2 flex flex-col">
                        <div class="flex items-center space-x-4 mb-8">
                            <span class="w-12 h-[2px] bg-red-700"></span>
                            <h2 class="text-[13px] font-bold tracking-[0.3em] text-red-700 uppercase">Administrasi Tambang</h2>
                        </div>
                        <h3 class="text-3xl font-oswald font-bold text-gray-900 mb-6 uppercase tracking-tight leading-tight">
                            Manajemen Izin & <br/><span class="text-red-700">Kepatuhan Operasional</span>
                        </h3>
                        <p class="text-gray-600 text-justify leading-loose text-[15px] mb-8 font-light">
                            Solusi terintegrasi untuk manajemen pengajuan izin kerja di lingkungan kerja PT LAZ COAL MANDIRI. Dirancang untuk meningkatkan efektivitas, transparansi, dan kecepatan proses administrasi SHE bagi seluruh mitra kerja.
                        </p>
                        <button onclick="showView('administrasi')" class="group flex items-center space-x-4 bg-red-700 text-white px-8 py-3.5 rounded-sm font-bold tracking-[0.2em] text-[12px] uppercase transition-all hover:bg-red-800 shadow-lg hover:shadow-xl">
                            <span>Akses Administrasi</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- VIEW: ADMINISTRASI -->
        <!-- VIEW: KESELAMATAN (INFORMASI) -->
        <div id="view-keselamatan" class="hidden flex flex-col min-h-screen transition-opacity duration-700 opacity-0">

            <!-- Hero Section -->
            <section class="relative w-full h-[500px] flex flex-col items-center justify-center overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/branding/remote/hero-operation.jpg') }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/30 to-[#f0f2f5]/30"></div>
                <div class="relative z-10 text-center text-white px-6 select-none">
                    <h1 class="text-[42px] md:text-[68px] font-oswald font-bold tracking-[0.06em] uppercase drop-shadow-xl leading-tight">
                        INFORMASI KESELAMATAN
                    </h1>
                    <h2 class="text-[34px] md:text-[54px] font-oswald font-bold tracking-[0.1em] uppercase drop-shadow-xl leading-tight mt-1">
                        PERTAMBANGAN
                    </h2>
                </div>
                <!-- Scroll down chevron -->
                <div class="absolute bottom-7 z-10 flex flex-col items-center">
                    <svg class="w-7 h-7 text-gray-700 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
                <!-- Back nav -->
                <button onclick="showView('portal')" class="absolute top-[88px] left-8 z-20 flex items-center space-x-2 text-white/70 hover:text-white transition-all group text-[11px] font-bold tracking-[0.2em] uppercase">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Portal Utama</span>
                </button>
            </section>

            <!-- Accordion Section -->
            <section class="bg-white flex-grow">
                <div class="max-w-[960px] mx-auto px-8 pb-28 pt-8">
                    <div class="grid grid-cols-1 divide-y divide-gray-100">
                        @php
                            // Categories untuk INFORMASI (exclude admin categories ID 1-4)
                            $adminCategoryIds = [1, 2, 3, 4];
                            $movedToAdminCodes = ['pengajuan_nomer_lambung', 'pengajuan_rambu', 'pengajuan_commisioning'];
                            $informasiCategories = array_filter($categories ?? [], function($cat) use ($adminCategoryIds) {
                                return !in_array((int)($cat['id'] ?? 0), $adminCategoryIds);
                            });
                            $informasiCategories = array_filter($informasiCategories, function($cat) use ($movedToAdminCodes) {
                                return !in_array((string)($cat['code'] ?? ''), $movedToAdminCodes, true);
                            });
                        @endphp

                        @if(count($informasiCategories) > 0)
                            @foreach($informasiCategories as $category)
                                @php
                                    $cat    = $category;
                                    $forms  = $formsByCategory[$cat['id']] ?? [];
                                    $accId  = 'acc-info-' . $cat['id'];
                                @endphp
                                @if($forms)
                                <div class="border-b border-gray-200">
                                    <button onclick="toggleAcc('{{ $accId }}')"
                                            id="btn-{{ $accId }}"
                                            class="w-full flex items-center justify-between py-5 text-left focus:outline-none group">
                                        <span class="text-[15px] font-medium text-gray-800 group-hover:text-gray-600 transition-colors">
                                            {{ $cat['name'] }}
                                        </span>
                                        <svg id="chev-{{ $accId }}"
                                             class="w-5 h-5 text-gray-500 flex-shrink-0 ml-3 transition-transform duration-300"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="{{ $accId }}" class="overflow-hidden transition-all duration-400 ease-in-out" style="max-height:0">
                                        <div class="pb-8 pr-2">
                                            <p class="text-[14px] text-gray-700 leading-relaxed mb-5">{{ $cat['description'] }}</p>
                                            <ul class="space-y-2">
                                                @foreach($forms as $idx => $form)
                                                <li>
                                                    <a href="{{ $form['form_url'] }}" target="_blank"
                                                       class="text-[14px] font-semibold text-[#005a73] underline underline-offset-2 decoration-[1px] hover:text-[#003d52] transition-colors">
                                                        {{ $idx + 1 }}. {{ $form['title'] }}
                                                    </a>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @else
                            <div class="py-6">
                                <p class="text-gray-700 text-[14px] leading-relaxed mb-6">
                                    Informasi keselamatan ditampilkan dari dokumen kebijakan resmi dan daftar persyaratan aktif pada sistem.
                                </p>

                                @if(!empty($policyDocuments ?? []))
                                    <div class="border-b border-gray-200">
                                        <button onclick="toggleAcc('acc-info-policy')"
                                                id="btn-acc-info-policy"
                                                class="w-full flex items-center justify-between py-5 text-left focus:outline-none group">
                                            <span class="text-[15px] font-medium text-gray-800 group-hover:text-gray-600 transition-colors">
                                                Dokumen Kebijakan Keselamatan
                                            </span>
                                            <svg id="chev-acc-info-policy"
                                                 class="w-5 h-5 text-gray-500 flex-shrink-0 ml-3 transition-transform duration-300"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div id="acc-info-policy" class="overflow-hidden transition-all duration-400 ease-in-out" style="max-height:0">
                                            <div class="pb-8 pr-2">
                                                <ul class="space-y-3">
                                                    @foreach(($policyDocuments ?? []) as $doc)
                                                        <li>
                                                            <a href="{{ route('portal.documents.show', ['code' => $doc['code']]) }}"
                                                               class="text-[14px] font-semibold text-[#005a73] underline underline-offset-2 decoration-[1px] hover:text-[#003d52] transition-colors">
                                                                {{ $doc['title'] }}
                                                            </a>
                                                            @if(!empty($doc['description']))
                                                                <p class="text-[13px] text-gray-600 mt-1">{{ $doc['description'] }}</p>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="border-b border-gray-200">
                                    <button onclick="toggleAcc('acc-info-simper-docs')"
                                            id="btn-acc-info-simper-docs"
                                            class="w-full flex items-center justify-between py-5 text-left focus:outline-none group">
                                        <span class="text-[15px] font-medium text-gray-800 group-hover:text-gray-600 transition-colors">
                                            Persyaratan Dokumen SIMPER
                                        </span>
                                        <svg id="chev-acc-info-simper-docs"
                                             class="w-5 h-5 text-gray-500 flex-shrink-0 ml-3 transition-transform duration-300"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="acc-info-simper-docs" class="overflow-hidden transition-all duration-400 ease-in-out" style="max-height:0">
                                        <div class="pb-8 pr-2">
                                            <ul class="space-y-2">
                                                @forelse(($requiredDocsMap['simper'] ?? []) as $idx => $doc)
                                                    <li class="text-[14px] text-gray-700">{{ $idx + 1 }}. {{ $doc['doc_name'] }}</li>
                                                @empty
                                                    <li class="text-[14px] text-gray-500">Belum ada persyaratan SIMPER aktif.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-b border-gray-200">
                                    <button onclick="toggleAcc('acc-info-mine-permit-docs')"
                                            id="btn-acc-info-mine-permit-docs"
                                            class="w-full flex items-center justify-between py-5 text-left focus:outline-none group">
                                        <span class="text-[15px] font-medium text-gray-800 group-hover:text-gray-600 transition-colors">
                                            Persyaratan Dokumen Mine Permit
                                        </span>
                                        <svg id="chev-acc-info-mine-permit-docs"
                                             class="w-5 h-5 text-gray-500 flex-shrink-0 ml-3 transition-transform duration-300"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="acc-info-mine-permit-docs" class="overflow-hidden transition-all duration-400 ease-in-out" style="max-height:0">
                                        <div class="pb-8 pr-2">
                                            <ul class="space-y-2">
                                                @forelse(($requiredDocsMap['mine_permit'] ?? []) as $idx => $doc)
                                                    <li class="text-[14px] text-gray-700">{{ $idx + 1 }}. {{ $doc['doc_name'] }}</li>
                                                @empty
                                                    <li class="text-[14px] text-gray-500">Belum ada persyaratan Mine Permit aktif.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- CTA: Link ke seluruh formulir --}}
                    <div class="mt-20 pt-12 border-t border-gray-100 flex justify-center">
                        <a href="{{ route('portal.forms') }}"
                           class="group relative inline-flex items-center space-x-3 bg-[#005a73] text-white px-10 py-3.5 rounded-full font-bold tracking-[0.18em] text-[11px] uppercase transition-all hover:bg-[#003d52] shadow-xl overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#005a73] to-[#003d52] translate-x-[-100%] transition-transform duration-500 group-hover:translate-x-0"></div>
                            <span class="relative z-10">Lihat Seluruh Direktori Formulir</span>
                            <svg class="relative z-10 w-4 h-4 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    <div id="view-administrasi" class="hidden flex flex-col min-h-screen transition-opacity duration-700 opacity-0">

        <!-- Hero Section — sesuai referensi: bg dump truck, judul besar centered, chevron scroll -->
        <section class="relative w-full h-[500px] flex flex-col items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/branding/remote/hero-hrga.jpg') }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/30 to-white/30"></div>
            <div class="relative z-10 text-center text-white px-6 select-none">
                <h1 class="text-[42px] md:text-[68px] font-oswald font-bold tracking-[0.06em] uppercase drop-shadow-xl leading-tight">
                    ADMINISTRASI KESELAMATAN
                </h1>
                <h2 class="text-[34px] md:text-[54px] font-oswald font-bold tracking-[0.1em] uppercase drop-shadow-xl leading-tight mt-1">
                    PERTAMBANGAN
                </h2>
            </div>
            <!-- Scroll down chevron sesuai referensi -->
            <div class="absolute bottom-7 z-10 flex flex-col items-center">
                <svg class="w-7 h-7 text-gray-700 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <!-- Back nav -->
            <button onclick="showView('portal')" class="absolute top-[88px] left-8 z-20 flex items-center space-x-2 text-white/70 hover:text-white transition-all group text-[11px] font-bold tracking-[0.2em] uppercase">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Portal Utama</span>
            </button>
        </section>

        <!-- Accordion Section — sesuai referensi: bg putih, grid 2-kol SIKK+PIMDO, SIMPER di bawah -->
        <section class="bg-white flex-grow">
            <div class="max-w-[960px] mx-auto px-8 pb-28 pt-2">

                @php
                    $adminCards = [
                        [
                            'key' => 'ikk',
                            'ids' => [1],
                            'label' => 'IKK (Izin Kerja Khusus)',
                        ],
                        [
                            'key' => 'visitor',
                            'ids' => [2],
                            'label' => 'Visitor & Temporary',
                        ],
                        [
                            'key' => 'simper',
                            'ids' => [3, 4],
                            'label' => 'SIMPER & Mine Permit',
                        ],
                    ];

                    // Pindahkan 3 kategori dari Informasi Keselamatan ke Administrasi Tambang.
                    $movedToAdminCodes = ['pengajuan_nomer_lambung', 'pengajuan_rambu', 'pengajuan_commisioning'];
                    $movedCards = [];
                    foreach ($movedToAdminCodes as $movedCode) {
                        $foundMovedCategory = collect($categories)->first(function ($category) use ($movedCode) {
                            return (string) ($category['code'] ?? '') === $movedCode;
                        });

                        if ($foundMovedCategory) {
                            $movedCards[] = [
                                'key' => 'moved-' . (string) ($foundMovedCategory['id'] ?? $movedCode),
                                'ids' => [(int) ($foundMovedCategory['id'] ?? 0)],
                                'label' => (string) ($foundMovedCategory['name'] ?? 'Kategori Pengajuan'),
                            ];
                        }
                    }

                    $adminCards = array_merge($adminCards, $movedCards);
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($adminCards as $card)
                        @php
                            $cardSections = [];

                            foreach ($card['ids'] as $categoryId) {
                                $foundCategory = collect($categories)->firstWhere('id', $categoryId);
                                $sectionForms = $formsByCategory[$categoryId] ?? [];

                                if ($foundCategory) {
                                    $cardSections[] = [
                                        'label' => (string) ($foundCategory['name'] ?? $card['label']),
                                        'description' => (string) ($foundCategory['description'] ?? ''),
                                        'forms' => $sectionForms,
                                    ];
                                }
                            }

                            if ($card['key'] === 'simper') {
                                $cardSections = [
                                    [
                                        'label' => 'SIMPER & Mine Permit',
                                        'description' => 'Kategori pengajuan baru, perpanjangan, rusak/hilang, dan monitoring SIMPER/Mine Permit.',
                                        'forms' => collect($formsByCategory[3] ?? [])->reject(fn($f) => (int)($f['id'] ?? 0) === 10)->values()->all(),
                                    ],
                                ];
                            }

                            $cardAccId = 'acc-admin-' . $card['key'];
                        @endphp

                        <div class="border-b border-gray-200 md:border-b-0 {{ ($loop->iteration % 3 === 0) ? 'md:border-r-0 md:pr-0' : 'md:border-r md:pr-8' }}">
                            <button onclick="toggleAcc('{{ $cardAccId }}')"
                                    id="btn-{{ $cardAccId }}"
                                    class="w-full flex items-center justify-between py-5 text-left focus:outline-none group">
                                <span class="text-[15px] font-medium text-gray-800 group-hover:text-red-700 transition-colors">
                                    {{ $card['label'] }}
                                </span>
                                <svg id="chev-{{ $cardAccId }}"
                                     class="w-5 h-5 text-gray-500 flex-shrink-0 ml-3 transition-transform duration-300"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="{{ $cardAccId }}" class="overflow-hidden transition-all duration-400 ease-in-out" style="max-height:0">
                                <div class="pb-8 pr-2">
                                    @foreach($cardSections as $sectionIndex => $section)
                                        <div class="{{ $sectionIndex > 0 ? 'mt-6 pt-6 border-t border-gray-100' : '' }}">
                                            <p class="text-[13px] font-semibold tracking-wide text-gray-500 uppercase mb-3">{{ $section['label'] }}</p>
                                            <p class="text-[14px] text-gray-700 leading-relaxed mb-5">{{ $section['description'] }}</p>
                                            
                                            @if($card['key'] === 'simper')
                                                <div class="mt-4">
                                                    <a href="{{ route('portal.simper') }}" 
                                                       class="group inline-flex items-center space-x-3 bg-red-700 text-white px-6 py-2.5 rounded-sm font-bold tracking-[0.1em] text-[11px] uppercase transition-all hover:bg-red-800 shadow-lg">
                                                        <span>Buka Portal SIMPER</span>
                                                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            @else
                                                <ul class="space-y-2">
                                                    @foreach($section['forms'] as $idx => $form)
                                                    <li>
                                                        <a href="{{ $form['form_url'] }}" target="_blank"
                                                           class="text-[14px] font-semibold text-[#1a73e8] underline underline-offset-2 decoration-[1px] hover:text-[#1557b0] transition-colors uppercase">
                                                            {{ $idx + 1 }}. {{ $form['title'] }}
                                                        </a>
                                                    </li>
                                                    @endforeach
                                                    @if($section['label'] === 'Pengajuan Internal')
                                                    <li>
                                                        <a href="javascript:void(0)" onclick="showView('sapkon')"
                                                           class="text-[13px] font-bold text-red-700 underline underline-offset-2 decoration-[1px] hover:text-red-900 transition-colors uppercase flex items-center gap-1 mt-3 tracking-wide">
                                                            <span>Buka Portal SIMPER SAPKON</span>
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- CTA: Link ke seluruh formulir --}}
                <div class="mt-20 pt-12 border-t border-gray-100 flex justify-center">
                    <a href="{{ route('portal.forms') }}"
                       class="group relative inline-flex items-center space-x-3 bg-[#1a252f] text-white px-10 py-3.5 rounded-full font-bold tracking-[0.18em] text-[11px] uppercase transition-all hover:bg-black shadow-xl overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-700 to-red-900 translate-x-[-100%] transition-transform duration-500 group-hover:translate-x-0"></div>
                        <span class="relative z-10">Lihat Seluruh Direktori Formulir</span>
                        <svg class="relative z-10 w-4 h-4 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    </div>

    <!-- VIEW: SAPKON -->
    <div id="view-sapkon" class="hidden flex flex-col min-h-screen transition-opacity duration-700 opacity-0">

        <!-- Hero Section -->
        <section class="relative w-full h-[500px] flex flex-col items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/branding/remote/about-sinergi.png') }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/30 to-[#f0f2f5]/30"></div>
            <div class="relative z-10 text-center text-white px-6 select-none">
                <h1 class="text-[42px] md:text-[68px] font-oswald font-bold tracking-[0.06em] uppercase drop-shadow-xl leading-tight">
                    BUCKET SAPKON
                </h1>
                <h2 class="text-[34px] md:text-[54px] font-oswald font-bold tracking-[0.1em] uppercase drop-shadow-xl leading-tight mt-1">
                    EKSTERNAL PORTAL
                </h2>
            </div>
            <!-- Scroll down chevron -->
            <div class="absolute bottom-7 z-10 flex flex-col items-center">
                <svg class="w-7 h-7 text-gray-700 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <!-- Back nav -->
            <button onclick="showView('administrasi')" class="absolute top-[88px] left-8 z-20 flex items-center space-x-2 text-white/70 hover:text-white transition-all group text-[11px] font-bold tracking-[0.2em] uppercase">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Administrasi Tambang</span>
            </button>
        </section>

        <!-- Accordion Section -->
        <section class="bg-white flex-grow">
            <div class="max-w-[960px] mx-auto px-8 pb-28 pt-8">
                <div class="border-b border-gray-200">
                    <button onclick="toggleAcc('acc-sapkon-forms')"
                            id="btn-acc-sapkon-forms"
                            class="w-full flex items-center justify-between py-5 text-left focus:outline-none group">
                        <span class="text-[15px] font-medium text-gray-800 group-hover:text-gray-600 transition-colors">
                            Daftar Formulir SAPKON
                        </span>
                        <svg id="chev-acc-sapkon-forms"
                             class="w-5 h-5 text-gray-500 flex-shrink-0 ml-3 transition-transform duration-300"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="acc-sapkon-forms" class="overflow-hidden transition-all duration-400 ease-in-out" style="max-height:0">
                        <div class="pb-8 pr-2">
                            <p class="text-[14px] text-gray-700 leading-relaxed mb-5">Daftar pengajuan khusus untuk mitra kerja Subkontraktor. Silakan pilih jenis pengajuan yang sesuai dengan kebutuhan Anda.</p>
                            <ul class="space-y-4">
                                <li>
                                    <a href="#" target="_blank"
                                       class="text-[14px] font-semibold text-[#1a73e8] underline underline-offset-2 decoration-[1px] hover:text-[#1557b0] transition-colors uppercase">
                                        1. PENGAJUAN SIMPER BARU (SAPKON)
                                    </a>
                                </li>
                                <li>
                                    <a href="#" target="_blank"
                                       class="text-[14px] font-semibold text-[#1a73e8] underline underline-offset-2 decoration-[1px] hover:text-[#1557b0] transition-colors uppercase">
                                        2. PERPANJANGAN SIMPER (SAPKON)
                                    </a>
                                </li>
                                <li>
                                    <a href="#" target="_blank"
                                       class="text-[14px] font-semibold text-[#1a73e8] underline underline-offset-2 decoration-[1px] hover:text-[#1557b0] transition-colors uppercase">
                                        3. PENGAJUAN MINE PERMIT (SAPKON)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Enterprise Footer -->
    <footer class="w-full relative bg-[#111] border-t-8 border-red-700 shadow-[0_-20px_50px_rgba(0,0,0,0.5)] mt-auto pt-20 pb-12">
        <div class="max-w-[1200px] mx-auto px-8">
            <div class="flex flex-col md:flex-row justify-between items-start gap-16 mb-20">
                <!-- Branding -->
                <div class="w-full md:w-1/3">
                    <div class="flex items-center space-x-5 mb-8">
                        <div class="relative w-[54px] h-[62px] bg-red-700 rounded-t-[45%] rounded-b-md border-b-4 border-red-900 flex flex-col items-center justify-center shadow-2xl">
                            <svg class="w-[18px] h-[18px] text-[#00ff00] absolute top-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                            <span class="text-white text-3xl font-black italic mt-1 drop-shadow-2xl">S</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-white text-2xl font-black tracking-[0.2em] drop-shadow-md leading-none mb-1">S-GUARDS</h3>
                            <p class="text-white/40 text-[10px] leading-tight font-bold uppercase tracking-[0.3em]">SHE DEPARTMENT SITE KINTAP</p>
                        </div>
                    </div>
                    <p class="text-white/40 text-[13px] leading-relaxed font-light text-justify">
                        Sistem manajemen keselamatan pertambangan terintegrasi PT LAZ COAL MANDIRI. Berkomitmen tinggi untuk menciptakan lingkungan kerja yang aman, sehat, dan produktif bagi seluruh insan pertambangan.
                    </p>
                </div>

                <!-- Contact/Dept -->
                <div class="w-full md:w-1/4">
                    <h4 class="text-white text-[12px] font-black tracking-[0.4em] uppercase mb-10 pb-4 border-b border-white/10">Department Info</h4>
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
                        <div class="text-[50px] font-black italic text-white/5 leading-none select-none">VISIONARY</div>
                        <div class="text-red-700 font-black text-2xl tracking-[0.3em] uppercase drop-shadow-lg">THINK SAFETY</div>
                        <div class="text-white font-black text-2xl tracking-[0.3em] uppercase">ACT SAFELY</div>
                        <div class="text-white/40 font-black text-xl tracking-[0.3em] uppercase">GET PRODUCTIVITY</div>
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
    
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1); }
        
        .carousel-dot { width: 10px; height: 10px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); border-radius: 50%; cursor: pointer; transition: all 0.5s ease; }
        .carousel-dot.active { width: 40px; background: #cc0000; border-color: #cc0000; border-radius: 10px; }
        
        #view-portal, #view-administrasi { transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
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

    <script>
        const carouselImages = [
            "{{ asset('assets/branding/remote/hero-operation.jpg') }}",
            "{{ asset('assets/branding/remote/about-sinergi.png') }}",
            "{{ asset('assets/branding/remote/hero-hrga.jpg') }}"
        ];
        let currentSlide = 0;
        let autoplayTimer = null;

        function initCarousel() {
            renderDots();
            startAutoplay();
            
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
        }

        function renderDots() {
            const container = document.getElementById('dotsContainer');
            if(!container) return;
            container.innerHTML = '';
            carouselImages.forEach((_, i) => {
                const dot = document.createElement('div');
                dot.className = `carousel-dot${i === currentSlide ? ' active' : ''}`;
                dot.onclick = () => goToSlide(i);
                container.appendChild(dot);
            });
        }

        function updateCarousel() {
            const img = document.getElementById('carouselImage');
            if(img) {
                img.style.opacity = '0';
                setTimeout(() => {
                    img.src = carouselImages[currentSlide];
                    img.style.opacity = '1';
                }, 300);
            }
            renderDots();
        }

        function nextSlide() { currentSlide = (currentSlide + 1) % carouselImages.length; updateCarousel(); resetAutoplay(); }
        function prevSlide() { currentSlide = (currentSlide - 1 + carouselImages.length) % carouselImages.length; updateCarousel(); resetAutoplay(); }
        function goToSlide(index) { currentSlide = index; updateCarousel(); resetAutoplay(); }
        function startAutoplay() { autoplayTimer = setInterval(() => { currentSlide = (currentSlide + 1) % carouselImages.length; updateCarousel(); }, 6000); }
        function resetAutoplay() { clearInterval(autoplayTimer); startAutoplay(); }

            function showView(view) {
                const portal = document.getElementById('view-portal');
                const admin = document.getElementById('view-administrasi');
                const keselamatan = document.getElementById('view-keselamatan');
                const sapkon = document.getElementById('view-sapkon');
            
                const hideView = (el) => {
                    if (!el) return;
                    el.style.opacity = '0';
                    setTimeout(() => { el.classList.add('hidden'); }, 400);
                };
            
                const showViewEl = (el) => {
                    if (!el) return;
                    el.classList.remove('hidden');
                    setTimeout(() => { el.style.opacity = '1'; }, 50);
                };
            
                if (view === 'administrasi') {
                    hideView(portal);
                    hideView(keselamatan);
                    hideView(sapkon);
                    setTimeout(() => { showViewEl(admin); }, 400);
                } else if (view === 'keselamatan') {
                    hideView(portal);
                    hideView(admin);
                    hideView(sapkon);
                    setTimeout(() => { showViewEl(keselamatan); }, 400);
                } else if (view === 'sapkon') {
                    hideView(portal);
                    hideView(admin);
                    hideView(keselamatan);
                    setTimeout(() => { showViewEl(sapkon); }, 400);
                } else {
                    hideView(admin);
                    hideView(keselamatan);
                    hideView(sapkon);
                    setTimeout(() => { showViewEl(portal); }, 400);
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

        function toggleAcc(id) {
            const content = document.getElementById(id);
            const chevron = document.getElementById('chev-' + id);
            const isOpen  = content.style.maxHeight !== '0px' && content.style.maxHeight !== '';

            // Close all accordions first
            document.querySelectorAll('[id^="acc-"]').forEach(function(el) {
                el.style.maxHeight = '0px';
            });
            document.querySelectorAll('[id^="chev-acc-"]').forEach(function(el) {
                el.style.transform = 'rotate(0deg)';
            });

            // Open clicked if it was closed
            if (!isOpen) {
                content.style.maxHeight = content.scrollHeight + 'px';
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }
        }

        document.addEventListener('DOMContentLoaded', initCarousel);
    </script>
</body>
</html>
