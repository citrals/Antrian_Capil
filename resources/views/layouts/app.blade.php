<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Antrean Online Disdukcapil Kabupaten Cirebon')</title>
    <meta name="description" content="Layanan pendaftaran antrean online resmi Dinas Kependudukan dan Pencatatan Sipil (Disdukcapil) Kabupaten Cirebon. Solusi cepat dan tertib tanpa antre panjang.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/kabcirebon.png') }}">

    <!-- Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js for lightweight, bulletproof interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .bg-grid-pattern {
            background-image: radial-gradient(rgba(14, 165, 233, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .gradient-border {
            position: relative;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.4));
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-emerald-500 selection:text-white flex flex-col min-h-screen"
      x-data="{ mobileMenuOpen: false, modalCekTiket: false, modalSyarat: false, modalCekKuota: false, activeSyaratSlug: 'ktp-el' }">

    <!-- Header & Identitas (Sesuai Spesifikasi Slide 1) -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition-all duration-300 print:hidden">
        <!-- Top Bar Notice -->
        <div class="bg-gradient-to-r from-emerald-700 via-teal-700 to-cyan-800 text-white text-xs py-1.5 px-4 hidden sm:block">
            <div class="max-w-7xl mx-auto flex justify-between items-center font-medium">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900 tracking-wide uppercase">Resmi</span>
                    <span>Portal Layanan Antrean Mandiri Disdukcapil Kabupaten Cirebon</span>
                </div>
                <div class="flex items-center gap-4 text-emerald-100 text-[11px]">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Senin - Jumat: 08:00 - 15:00 WIB
                    </span>
                    <span class="text-emerald-400">|</span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Sumber, Cirebon
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Navigation Bar -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Header Identitas: Logo Kabupaten Cirebon di sebelah kiri & Judul -->
                <a href="{{ route('home') }}" class="flex items-center gap-3.5 group focus:outline-hidden">
                    <div class="relative w-12 h-14 sm:w-13 sm:h-15 shrink-0 transition-transform duration-300 group-hover:scale-105 drop-shadow-sm">
                        <img src="{{ asset('images/kabcirebon.png') }}" 
                             alt="Logo Kabupaten Cirebon" 
                             class="w-full h-full object-contain"
                             loading="eager">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[11px] sm:text-xs font-bold tracking-wider uppercase text-emerald-700">Pemerintah Kabupaten Cirebon</span>
                        <h1 class="text-base sm:text-xl font-extrabold text-slate-900 tracking-tight leading-tight group-hover:text-emerald-700 transition-colors">
                            Disdukcapil Kabupaten Cirebon
                        </h1>
                        <span class="text-[10px] sm:text-xs text-slate-500 font-medium">Dinas Kependudukan dan Pencatatan Sipil</span>
                    </div>
                </a>

                <!-- Navigasi Sederhana: Beranda, Cek Kuota, Syarat Berkas, Cek Tiket -->
                <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                    <a href="{{ route('home') }}" 
                       class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors {{ request()->routeIs('home') ? 'text-emerald-700 bg-emerald-50/80 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Beranda
                    </a>
                    
                    <a href="{{ route('home') }}#cek-kuota" 
                       class="px-3.5 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        Cek Kuota
                    </a>

                    <a href="{{ route('home') }}#syarat-berkas" 
                       class="px-3.5 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        Syarat Berkas
                    </a>

                    <a href="{{ route('ticket.search') }}" 
                       class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5 {{ request()->routeIs('ticket.search') ? 'text-emerald-700 bg-emerald-50/80 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>Cek Tiket</span>
                    </a>
                </nav>

                <!-- Action Button in Header -->
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ route('registration.create') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-bold shadow-md shadow-emerald-600/20 hover:shadow-lg hover:shadow-emerald-600/30 transition-all duration-200 transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Daftar Antrean</span>
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex md:hidden">
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-hidden transition-colors"
                            aria-label="Buka Menu">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav Menu Dropdown -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-b border-slate-200 bg-white/95 backdrop-blur-md px-4 pt-2 pb-5 space-y-2">
            <a href="{{ route('home') }}" 
               @click="mobileMenuOpen = false"
               class="block px-4 py-2.5 rounded-lg text-base font-semibold {{ request()->routeIs('home') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700 hover:bg-slate-100' }}">
                Beranda
            </a>
            <a href="{{ route('home') }}#cek-kuota" 
               @click="mobileMenuOpen = false"
               class="block px-4 py-2.5 rounded-lg text-base font-semibold text-slate-700 hover:bg-slate-100">
                Cek Kuota
            </a>
            <a href="{{ route('home') }}#syarat-berkas" 
               @click="mobileMenuOpen = false"
               class="block px-4 py-2.5 rounded-lg text-base font-semibold text-slate-700 hover:bg-slate-100">
                Syarat Berkas
            </a>
            <a href="{{ route('ticket.search') }}" 
               @click="mobileMenuOpen = false" 
               class="w-full text-left px-4 py-2.5 rounded-lg text-base font-semibold flex items-center justify-between {{ request()->routeIs('ticket.search') ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-slate-700 hover:bg-slate-100' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    <span>Cek Status / Cari Tiket</span>
                </span>
                <span class="text-[11px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-md">Slide 5</span>
            </a>
            <div class="pt-2">
                <a href="{{ route('registration.create') }}" 
                   class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold shadow-md shadow-emerald-600/20 text-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Daftar Antrean Online Sekarang</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="grow">
        @yield('content')
    </main>

    <!-- Modal Cek Tiket (Interactive Global Modal) -->
    <div x-show="modalCekTiket" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="modalCekTiket = false">
        
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200"
             @click.outside="modalCekTiket = false"
             x-data="{
                keyword: '',
                loading: false,
                result: null,
                errorMsg: null,
                searchTicket() {
                    if (this.keyword.length < 4) {
                        this.errorMsg = 'Masukkan minimal 4 karakter (Nomor Tiket atau NIK)';
                        return;
                    }
                    this.loading = true;
                    this.errorMsg = null;
                    this.result = null;

                    fetch('{{ route('ticket.check') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({ keyword: this.keyword })
                    })
                    .then(res => res.json().then(data => ({ status: res.status, body: data })))
                    .then(({ status, body }) => {
                        this.loading = false;
                        if (status === 200 && body.status === 'success') {
                            this.result = body.data;
                        } else {
                            this.errorMsg = body.message || 'Tiket tidak ditemukan.';
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        this.errorMsg = 'Terjadi gangguan jaringan. Coba beberapa saat lagi.';
                    });
                }
             }">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-700 to-teal-800 p-5 text-white flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-tight">Cek Status Tiket Antrean</h3>
                        <p class="text-xs text-emerald-200">Lacak nomor antrean dan jadwal kunjungan Anda</p>
                    </div>
                </div>
                <button type="button" @click="modalCekTiket = false" class="p-1.5 rounded-lg text-emerald-200 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nomor Tiket atau 16 Digit NIK
                    </label>
                    <div class="relative">
                        <input type="text" 
                               x-model="keyword"
                               @keydown.enter="searchTicket()"
                               placeholder="Contoh: TKT-KTP-2026... atau 3209..." 
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium pr-24">
                        <button type="button" 
                                @click="searchTicket()"
                                :disabled="loading"
                                class="absolute right-2 top-2 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-lg text-xs font-bold transition-colors">
                            <span x-show="!loading">Cari</span>
                            <span x-show="loading" x-cloak>Mencari...</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5">Tips: Anda dapat mencari berdasarkan kode booking tiket atau NIK yang didaftarkan.</p>
                </div>

                <!-- Error Message -->
                <div x-show="errorMsg" x-cloak class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-start gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="errorMsg"></span>
                </div>

                <!-- Ticket Result Card -->
                <div x-show="result" x-cloak class="border border-emerald-200 bg-emerald-50/50 rounded-2xl p-4 space-y-3">
                    <div class="flex justify-between items-center pb-3 border-b border-emerald-200">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Nomor Antrean Anda</span>
                            <div class="text-3xl font-black text-emerald-800" x-text="result?.queue_number"></div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-200 text-emerald-800" x-text="result?.status_badge"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-500">Kode Tiket</span>
                            <p class="font-bold text-slate-900 font-mono" x-text="result?.ticket_number"></p>
                        </div>
                        <div>
                            <span class="text-slate-500">Layanan</span>
                            <p class="font-bold text-slate-900" x-text="result?.service_name"></p>
                        </div>
                        <div>
                            <span class="text-slate-500">Nama Pemohon</span>
                            <p class="font-bold text-slate-900" x-text="result?.full_name"></p>
                        </div>
                        <div>
                            <span class="text-slate-500">NIK Pemohon</span>
                            <p class="font-bold text-slate-900 font-mono" x-text="result?.nik_masked"></p>
                        </div>
                        <div class="col-span-2 bg-white p-2.5 rounded-xl border border-emerald-200">
                            <span class="text-slate-500 text-[11px] block">Jadwal & Shift Kunjungan:</span>
                            <p class="font-bold text-emerald-900 text-sm" x-text="result?.appointment_date"></p>
                            <p class="text-xs text-emerald-700 font-semibold" x-text="result?.shift_name"></p>
                        </div>
                    </div>

                    <div class="pt-2 text-center">
                        <a :href="'/tiket/' + result?.ticket_number" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 underline">
                            <span>Buka Halaman Bukti Tiket Lengkap & QR Code</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end">
                <button type="button" @click="modalCekTiket = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-lg">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 text-sm mt-16 print:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Identity & Description -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/kabcirebon.png') }}" alt="Logo Kab Cirebon" class="w-12 h-14 object-contain">
                        <div>
                            <h4 class="font-black text-white text-base leading-tight">Disdukcapil Kabupaten Cirebon</h4>
                            <p class="text-xs text-emerald-400 font-medium">Dinas Kependudukan dan Pencatatan Sipil</p>
                            <p class="text-[11px] text-slate-400">Pemerintah Kabupaten Cirebon, Jawa Barat</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed pr-4">
                        Sistem Antrean Online ini merupakan layanan resmi sebagai solusi alternatif bagi warga Kabupaten Cirebon yang membutuhkan kepastian jadwal pelayanan administrasi kependudukan tanpa harus berebut antrean fisik di kantor dinas.
                    </p>
                    <div class="flex items-center gap-2 text-xs text-emerald-400 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sistem Online Aktif 24 Jam • Pelayanan Loket Hari Kerja</span>
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat -->
                <div class="space-y-3">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider">Navigasi Utama</h5>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('home') }}" class="hover:text-emerald-400 transition-colors">Beranda / Utama</a></li>
                        <li><a href="{{ route('registration.create') }}" class="hover:text-emerald-400 transition-colors">Daftar Antrean Online</a></li>
                        <li><a href="{{ route('home') }}#cek-kuota" class="hover:text-emerald-400 transition-colors">Informasi Kuota Real-Time</a></li>
                        <li><a href="{{ route('home') }}#syarat-berkas" class="hover:text-emerald-400 transition-colors">Panduan Syarat Dokumen</a></li>
                        <li><button type="button" @click="modalCekTiket = true" class="hover:text-emerald-400 transition-colors text-left">Lacak Tiket & Nomor Antrean</button></li>
                    </ul>
                </div>

                <!-- Col 3: Lokasi & Kontak -->
                <div class="space-y-3">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider">Kontak & Lokasi</h5>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span class="leading-relaxed">Jl. Sunan Muria Komplek Pemda No. 04, Sumber, Kec. Sumber, Kabupaten Cirebon, Jawa Barat 45611</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="https://wa.me/6285194268118" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-400 transition-colors">
                                WhatsApp: <span class="font-medium text-slate-300">(+62) 85194268118</span>
                            </a>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:disdukcapil@cirebonkab.go.id" class="hover:text-emerald-400 transition-colors">
                                Email: <span class="text-slate-300">disdukcapil@cirebonkab.go.id</span>
                            </a>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            <a href="https://disdukcapil.cirebonkab.go.id" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-400 transition-colors">
                                Website: <span class="text-slate-300">disdukcapil.cirebonkab.go.id</span>
                            </a>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            <a href="https://instagram.com/disdukcapilkabcirebon" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-400 transition-colors">
                                Instagram: <span class="text-slate-300">@disdukcapilkabcirebon</span>
                            </a>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            <a href="https://www.youtube.com/results?search_query=Disdukcapil+Kabupaten+Cirebon" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-400 transition-colors">
                                YouTube: <span class="text-slate-300">Disdukcapil Kabupaten Cirebon</span>
                            </a>
                        </li>
                        <li class="flex items-center gap-2 pt-1 border-t border-slate-800/80">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Jam Operasional: 08:00 - 15:00 WIB</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-800 text-center text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-2">
                <p>&copy; {{ date('Y') }} Dinas Kependudukan dan Pencatatan Sipil Kabupaten Cirebon. Hak Cipta Dilindungi.</p>
                <p class="text-[11px] text-slate-400">Semboyan: <span class="text-amber-400 font-semibold italic">"Rame Ing Gawe, Suci Ing Pamrih"</span></p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
