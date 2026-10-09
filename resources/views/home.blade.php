@extends('layouts.app')

@section('title', 'Beranda - Antrean Online Disdukcapil Kabupaten Cirebon')

@section('content')
<div class="relative overflow-hidden">
    <!-- Hero Background Graphic Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[620px] pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-32 -left-20 w-96 h-96 bg-emerald-400/20 rounded-full blur-3xl"></div>
        <div class="absolute top-10 right-0 w-[480px] h-[480px] bg-teal-300/20 rounded-full blur-3xl"></div>
        <div class="absolute top-80 left-1/3 w-80 h-80 bg-cyan-400/15 rounded-full blur-3xl"></div>
    </div>

    <!-- HERO SECTION & INFORMASI OPERASIONAL (SLIDE 1) -->
    <section class="relative z-10 pt-8 sm:pt-14 pb-16 lg:pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- KOTAK PENGUMUMAN & STATUS OPERASIONAL INTERAKTIF (Slide 1 Requirement) -->
            <div class="mb-8 lg:mb-12" x-data="{ alertOpen: true, activeTab: 0 }">
                <div x-show="alertOpen" 
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="rounded-2xl border {{ $operationalStatus['is_open'] ? 'border-emerald-200 bg-gradient-to-r from-emerald-50/90 via-teal-50/80 to-white' : 'border-amber-200 bg-gradient-to-r from-amber-50/95 via-rose-50/80 to-white' }} shadow-sm backdrop-blur-md p-4 sm:p-5 transition-all">
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <!-- Status Indicator & Live Badge -->
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="relative shrink-0">
                                <div class="w-11 h-11 rounded-2xl {{ $operationalStatus['is_open'] ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white' }} flex items-center justify-center shadow-md">
                                    @if($operationalStatus['is_open'])
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @endif
                                </div>
                                <span class="absolute -bottom-1 -right-1 flex h-3.5 w-3.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $operationalStatus['is_open'] ? 'bg-emerald-400' : 'bg-amber-400' }} opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 {{ $operationalStatus['is_open'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                </span>
                            </div>

                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $operationalStatus['is_open'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }} uppercase tracking-wider">
                                        Status Hari Ini: {{ $operationalStatus['status_badge'] }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $operationalStatus['time_range'] }}
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-700 font-medium">
                                    {{ $operationalStatus['status_text'] }}
                                </p>
                            </div>
                        </div>

                        <!-- Interactive Announcement Slider / Controls -->
                        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                            <button type="button" 
                                    @click="alertOpen = false" 
                                    class="text-xs text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-black/5 transition-colors"
                                    title="Sembunyikan Pengumuman">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Pengumuman Tambahan Interaktif Tabs -->
                    <div class="mt-4 pt-3.5 border-t border-slate-200/80 grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($announcements as $index => $item)
                            <div class="p-2.5 rounded-xl bg-white/70 border border-slate-200/60 text-xs hover:bg-white transition-all shadow-2xs">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-bold text-slate-800 line-clamp-1">{{ $item['title'] }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold shrink-0 {{ $item['badge_color'] === 'amber' ? 'bg-amber-100 text-amber-800' : ($item['badge_color'] === 'emerald' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $item['badge'] }}
                                    </span>
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed line-clamp-2">
                                    {{ $item['content'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- HERO MAIN GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- Left Column: Sapaan Ramah, Kalimat Sambutan & CTA Besar Mencolok -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    
                    <!-- Friendly Greeting Badge -->
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white border border-emerald-200/90 shadow-xs">
                        <span class="text-xl">👋</span>
                        <span class="text-xs sm:text-sm font-bold text-emerald-800">
                            Sampurasun Warga Kabupaten Cirebon!
                        </span>
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="text-xs text-slate-500 font-semibold hidden sm:inline">Pelayanan Cepat & Tertib</span>
                    </div>

                    <!-- Hero Main Heading -->
                    <div class="space-y-3">
                        <h2 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight leading-[1.15]">
                            Solusi Cerdas Antrean Kependudukan
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600">
                                Tanpa Berebut Nomor Fisik
                            </span>
                        </h2>
                        
                        <!-- Kalimat Sambutan & Penjelasan Solusi Alternatif Kuota Offline -->
                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl font-normal">
                            Selamat datang di portal pelayanan mandiri <strong class="text-slate-800">Disdukcapil Kabupaten Cirebon</strong>. 
                            Web ini dirancang khusus sebagai <span class="bg-amber-100 text-amber-900 px-1.5 py-0.5 rounded-md font-semibold">solusi alternatif resmi</span> bagi warga yang kehabisan nomor antrean offline di kantor dinas, sehingga Anda dapat memilih hari dan jam kunjungan secara pasti dari rumah.
                        </p>
                    </div>

                    <!-- Highlight Features Pills -->
                    <div class="flex flex-wrap gap-2.5 pt-1">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100/90 border border-slate-200 text-xs font-semibold text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Pilih Shift (Pagi & Siang)</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100/90 border border-slate-200 text-xs font-semibold text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Validasi Otomatis Hari Libur</span>
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100/90 border border-slate-200 text-xs font-semibold text-slate-700">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>E-Tiket QR Code Resmi</span>
                        </div>
                    </div>

                    <!-- CALL TO ACTION (CTA) BESAR MENCOLOK (Sesuai Spesifikasi Slide 1) -->
                    <div class="pt-2 sm:pt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a href="{{ route('registration.create') }}" 
                           class="group relative inline-flex items-center justify-center gap-3 px-8 py-4 sm:py-4.5 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-base sm:text-lg shadow-xl shadow-emerald-700/25 hover:shadow-2xl hover:shadow-emerald-600/35 transition-all duration-300 transform hover:-translate-y-1 active:translate-y-0 text-center">
                            <span class="absolute inset-0 w-full h-full rounded-2xl bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></span>
                            <svg class="w-6 h-6 text-white transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="tracking-wide">Daftar Antrean Online Sekarang</span>
                            <svg class="w-5 h-5 text-emerald-200 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>

                        <a href="#cek-kuota" 
                           class="inline-flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 font-bold text-sm sm:text-base border border-slate-300/80 shadow-xs hover:shadow-md transition-all">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Cek Sisa Kuota</span>
                        </a>
                    </div>

                    <!-- Small Security & Guarantee Notice -->
                    <div class="flex items-center gap-2 text-xs text-slate-500 pt-1">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Layanan 100% Bebas Biaya (Gratis), Bebas Pungli, Terkoneksi dengan Database Kependudukan Ditjen Dukcapil.</span>
                    </div>
                </div>

                <!-- Right Column: Interactive Quick Card / Operational Stats -->
                <div class="lg:col-span-5">
                    <div class="relative bg-white/90 backdrop-blur-xl rounded-3xl p-6 sm:p-7 border border-slate-200/90 shadow-xl space-y-6">
                        <!-- Top Card Header -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div>
                                <span class="text-[11px] font-bold text-emerald-700 tracking-wider uppercase">Jadwal Aktif Terdekat</span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5 tracking-tight">
                                    {{ $targetDate->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </h3>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Kuota Aktif
                            </span>
                        </div>

                        <!-- 4 Layanan Quick Overview -->
                        <div class="space-y-3">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Ketersediaan Kuota Layanan:</span>
                            
                            @foreach($services as $svc)
                                @php
                                    $info = $quotaSummary[$svc->id] ?? null;
                                    $rem = $info['remaining'] ?? 0;
                                    $tot = $info['total'] ?? 140;
                                    $pct = $tot > 0 ? round(($rem / $tot) * 100) : 0;
                                    $svcTitle = match($svc->slug) {
                                        'ktp-el' => 'PELAYAN ADMINDUK',
                                        'kartu-keluarga' => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                                        'akta-kelahiran-kematian' => 'KONSULTASI / PENGADUAN',
                                        'pindah-datang' => 'LOKET PRIORITAS',
                                        default => $svc->name,
                                    };
                                @endphp
                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-emerald-300 transition-colors">
                                    <div class="flex justify-between items-center mb-1.5">
                                        <div class="flex items-center gap-2">
                                            @if($svc->slug === 'ktp-el')
                                                <span class="text-emerald-600 font-bold">💳</span>
                                            @elseif($svc->slug === 'kartu-keluarga')
                                                <span class="text-blue-600 font-bold">👨‍👩‍👧‍👦</span>
                                            @elseif($svc->slug === 'akta-kelahiran-kematian')
                                                <span class="text-amber-600 font-bold">📜</span>
                                            @else
                                                <span class="text-teal-600 font-bold">📍</span>
                                            @endif
                                            <span class="text-xs font-bold text-slate-800">{{ $svcTitle }}</span>
                                        </div>
                                        <span class="text-xs font-extrabold {{ $rem > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                            {{ $rem > 0 ? "Sisa {$rem} Kuota" : 'Penuh' }}
                                        </span>
                                    </div>
                                    <!-- Mini Progress Bar -->
                                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full {{ $rem > 20 ? 'bg-emerald-500' : ($rem > 0 ? 'bg-amber-500' : 'bg-rose-500') }} transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Quick Action Button to Book -->
                        <div class="pt-2">
                            <a href="{{ route('registration.create') }}" 
                               class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-slate-900 hover:bg-emerald-800 text-white text-xs font-extrabold transition-colors">
                                <span>Pilih Layanan & Jadwal Kunjungan</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION: 4 LANGKAH MUDAH PENDAFTARAN (Alur Pengguna) -->
    <section class="py-14 bg-white border-y border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Alur Mudah & Transparan
                </span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                    4 Langkah Mendapatkan Nomor Antrean
                </h3>
                <p class="text-sm text-slate-600 mt-2">
                    Proses mandiri kurang dari 2 menit untuk mendapatkan kepastian jadwal pelayanan di loket Disdukcapil Cirebon.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Step 1 -->
                <div class="relative bg-slate-50/80 p-6 rounded-2xl border border-slate-200 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-lg mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        1
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-1">Pilih Jenis Layanan</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Tentukan kebutuhan dokumen: Pembuatan KTP-el, Kartu Keluarga, Akta Pencatatan Sipil, atau Surat Pindah Datang.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="relative bg-slate-50/80 p-6 rounded-2xl border border-slate-200 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-black text-lg mb-4 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        2
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-1">Pilih Tanggal & Shift</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pilih jadwal pada kalender interaktif (hari libur/akhir pekan otomatis terblokir) dan pilih sesi shift yang kuotanya masih tersedia.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="relative bg-slate-50/80 p-6 rounded-2xl border border-slate-200 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-black text-lg mb-4 group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                        3
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-1">Isi 16 Digit NIK & Data</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Masukkan Nomor Induk Kependudukan (NIK) pemohon, nama lengkap, dan nomor WhatsApp aktif untuk verifikasi kedatangan.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="relative bg-slate-50/80 p-6 rounded-2xl border border-slate-200 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black text-lg mb-4 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        4
                    </div>
                    <h4 class="text-base font-bold text-slate-900 mb-1">Dapatkan Bukti Tiket QR</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Simpan nomor antrean dan kode QR pada layar ponsel Anda untuk dipindai oleh petugas loket saat hari kunjungan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: CEK KUOTA REAL-TIME (Anchor: #cek-kuota) -->
    <section id="cek-kuota" class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase bg-emerald-100/70 px-3 py-1 rounded-full border border-emerald-300">
                        Monitoring Kuota Antrean
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                        Pilihan Layanan & Kuota Hari Ini
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">
                        Sistem memperbarui kuota secara real-time berdasarkan pemohon yang telah mendaftar online.
                    </p>
                </div>

                <a href="{{ route('registration.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition-colors self-start md:self-auto">
                    <span>Langsung Daftar Layanan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            @php
                $customTitles = [
                    'ktp-el' => 'PELAYAN ADMINDUK',
                    'kartu-keluarga' => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                    'akta-kelahiran-kematian' => 'KONSULTASI / PENGADUAN',
                    'pindah-datang' => 'LOKET PRIORITAS',
                ];
                $titleByOriginalName = [
                    'Pembuatan KTP-el' => 'PELAYAN ADMINDUK',
                    'Kartu Keluarga (KK)' => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                    'Akta Kelahiran / Kematian' => 'KONSULTASI / PENGADUAN',
                    'Surat Pindah Datang (SKPWNI)' => 'LOKET PRIORITAS',
                ];
            @endphp

            <!-- Services Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($services as $service)
                    @php
                        $info = $quotaSummary[$service->id] ?? null;
                        $shifts = $info['shifts'] ?? [];
                        $cardTitle = $customTitles[$service->slug] ?? ($titleByOriginalName[$service->name] ?? match ($loop->iteration) {
                            1 => 'PELAYAN ADMINDUK',
                            2 => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                            3 => 'KONSULTASI / PENGADUAN',
                            4 => 'LOKET PRIORITAS',
                            default => $service->name,
                        });
                    @endphp
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <!-- Header card (Top Icon & Badge) -->
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                    @if($service->slug === 'ktp-el')
                                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                    @elseif($service->slug === 'kartu-keluarga')
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    @elseif($service->slug === 'akta-kelahiran-kematian')
                                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @else
                                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    @endif
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $service->badge ?? 'Reguler' }}
                                </span>
                            </div>

                            <!-- Kotak Header Judul Layanan (Rata Tengah & Ukuran Pas Sesuai Permintaan) -->
                            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl px-3 py-2.5 min-h-[66px] flex flex-col items-center justify-center text-center mb-2.5 shadow-2xs">
                                <h4 class="font-extrabold text-slate-900 text-center uppercase tracking-tight group-hover:text-emerald-700 transition-colors {{ strlen($cardTitle) > 25 ? 'text-xs sm:text-[13px] leading-snug' : 'text-sm sm:text-base leading-tight' }}">
                                    {{ $cardTitle }}
                                </h4>
                            </div>

                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 text-center">
                                {{ $service->description }}
                            </p>

                            <!-- Shift Quota Details -->
                            <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                                @foreach($shifts as $shift)
                                    <div class="flex items-center justify-between text-[11px] p-2 rounded-xl {{ $shift['is_full'] ? 'bg-rose-50 text-rose-700 border border-rose-100' : 'bg-slate-50 text-slate-700' }}">
                                        <div class="font-medium">
                                            <span class="font-bold">{{ $shift['name'] }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ $shift['time'] }}</span>
                                        </div>
                                        <span class="font-bold {{ $shift['is_full'] ? 'text-rose-600 bg-rose-100 px-2 py-0.5 rounded' : 'text-emerald-700' }}">
                                            {{ $shift['status_label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- CTA to book this specific service -->
                        <div class="pt-5 mt-2">
                            <a href="{{ route('registration.create', ['service' => $service->slug]) }}" 
                               class="w-full flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-800 hover:text-white font-bold text-xs transition-colors">
                                <span>Pilih Layanan Ini</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SECTION: SYARAT BERKAS PERSYARATAN (Anchor: #syarat-berkas) -->
    <section id="syarat-berkas" class="py-16 bg-white border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-bold tracking-wider text-emerald-700 uppercase bg-emerald-100/70 px-3 py-1 rounded-full border border-emerald-300">
                    Panduan Berkas Dokumen
                </span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                    Persyaratan Berkas Pelayanan
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Pastikan Anda membawa seluruh dokumen asli dan fotokopi yang disyaratkan saat hadir di loket Disdukcapil.
                </p>
            </div>

            <!-- Interactive Tab Selection for Requirements -->
            <div x-data="{ currentTab: 'ktp-el' }" class="max-w-4xl mx-auto">
                <div class="flex flex-wrap items-center justify-center gap-2 p-1.5 bg-slate-100 rounded-2xl mb-8">
                    @foreach($services as $s)
                        <button type="button" 
                                @click="currentTab = '{{ $s->slug }}'"
                                :class="currentTab === '{{ $s->slug }}' ? 'bg-white text-emerald-800 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm transition-all cursor-pointer">
                            {{ $customTitles[$s->slug] ?? ($titleByOriginalName[$s->name] ?? $s->name) }}
                        </button>
                    @endforeach
                </div>

                @foreach($services as $s)
                    <div x-show="currentTab === '{{ $s->slug }}'" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-slate-50 border border-slate-200 rounded-3xl p-6 sm:p-8 space-y-4">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                            <div>
                                <h4 class="text-lg font-bold text-slate-900">{{ $customTitles[$s->slug] ?? ($titleByOriginalName[$s->name] ?? $s->name) }}</h4>
                                <p class="text-xs text-slate-500">{{ $s->subtitle }}</p>
                            </div>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                Estimasi Waktu Loket: &plusmn; {{ $s->estimated_minutes }} Menit
                            </span>
                        </div>

                        <div class="space-y-2.5 pt-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block">Daftar Dokumen Wajib Dibawa:</span>
                            @if(is_array($s->requirements))
                                @foreach($s->requirements as $index => $req)
                                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white border border-slate-200/80 text-xs text-slate-700">
                                        <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">
                                            {{ $index + 1 }}
                                        </div>
                                        <span>{{ $req }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="pt-4 flex justify-end">
                            <a href="{{ route('registration.create', ['service' => $s->slug]) }}" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors">
                                <span>Daftar Antrean {{ $customTitles[$s->slug] ?? ($titleByOriginalName[$s->name] ?? $s->name) }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- BOTTOM CTA BANNER -->
    <section class="py-14 bg-gradient-to-r from-emerald-800 via-teal-800 to-cyan-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-4">
            <h3 class="text-2xl sm:text-4xl font-black">Siap Mengurus Dokumen Kependudukan Anda?</h3>
            <p class="text-xs sm:text-base text-emerald-100 max-w-2xl mx-auto">
                Daftar antrean online sekarang untuk mendapatkan kepastian kuota, tanggal, dan shift waktu tanpa perlu menunggu berjam-jam di kantor.
            </p>
            <div class="pt-2">
                <a href="{{ route('registration.create') }}" 
                   class="inline-flex items-center gap-2 px-8 py-4 bg-white text-emerald-900 hover:bg-emerald-50 rounded-2xl text-base font-extrabold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                    <span>Mulai Pendaftaran Antrean Online</span>
                    <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
