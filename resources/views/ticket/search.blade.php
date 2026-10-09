@extends('layouts.app')

@section('title', 'Cek Status & Cari Tiket Antrean - Disdukcapil Kabupaten Cirebon')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="{
        searchTab: 'nik', // 'nik', 'phone', or 'code'
        keyword: '{{ $prefilled ?? '' }}',
        loading: false,
        errorMessage: '',
        hasSearched: false,
        searchResult: null,
        allTickets: [],
        localTickets: [],
        lastLocalTicket: null,
        copiedIndex: null,

        init() {
            this.loadLocalStorage();
            if (this.keyword && this.keyword.length >= 3) {
                // Determine tab based on prefilled keyword
                if (/^\d{16}$/.test(this.keyword)) {
                    this.searchTab = 'nik';
                } else if (/^(08|628|\+628|\d{9,14})$/.test(this.keyword)) {
                    this.searchTab = 'phone';
                } else {
                    this.searchTab = 'code';
                }
                this.doSearch();
            }
        },

        loadLocalStorage() {
            try {
                const rawTickets = localStorage.getItem('capil_active_tickets');
                if (rawTickets) {
                    this.localTickets = JSON.parse(rawTickets);
                }
                const rawLast = localStorage.getItem('capil_last_ticket');
                if (rawLast) {
                    this.lastLocalTicket = JSON.parse(rawLast);
                }
            } catch (e) {
                console.warn('Gagal membaca LocalStorage:', e);
            }
        },

        clearLocalHistory() {
            if (confirm('Hapus riwayat tiket yang tersimpan di perangkat ini?')) {
                localStorage.removeItem('capil_active_tickets');
                localStorage.removeItem('capil_last_ticket');
                this.localTickets = [];
                this.lastLocalTicket = null;
            }
        },

        setTab(tab) {
            this.searchTab = tab;
            this.errorMessage = '';
        },

        copyText(text, index) {
            navigator.clipboard.writeText(text);
            this.copiedIndex = index;
            setTimeout(() => this.copiedIndex = null, 2000);
        },

        async doSearch() {
            const query = this.keyword.trim();
            this.errorMessage = '';

            if (!query) {
                this.errorMessage = 'Mohon masukkan NIK, Nomor WhatsApp, atau Kode Tiket Anda.';
                return;
            }

            if (this.searchTab === 'nik' && query.replace(/\D/g, '').length < 16) {
                this.errorMessage = 'NIK harus berjumlah tepat 16 digit angka sesuai KTP/KK.';
                return;
            }

            if (this.searchTab === 'phone' && query.replace(/\D/g, '').length < 9) {
                this.errorMessage = 'Nomor WhatsApp / HP minimal 9 digit angka.';
                return;
            }

            this.loading = true;
            this.hasSearched = true;
            this.searchResult = null;
            this.allTickets = [];

            try {
                const response = await fetch('{{ route('ticket.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ keyword: query })
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    this.searchResult = data.data;
                    this.allTickets = data.tickets || [data.data];

                    // Automatically save found active ticket to local storage for future quick recall
                    try {
                        let stored = JSON.parse(localStorage.getItem('capil_active_tickets') || '[]');
                        this.allTickets.forEach(t => {
                            stored = stored.filter(x => x.ticket_number !== t.ticket_number);
                            stored.unshift(t);
                        });
                        localStorage.setItem('capil_active_tickets', JSON.stringify(stored.slice(0, 10)));
                        localStorage.setItem('capil_last_ticket', JSON.stringify(this.searchResult));
                        this.loadLocalStorage();
                    } catch (e) {
                        console.warn('LocalStorage save error:', e);
                    }
                } else {
                    this.errorMessage = data.message || 'Data tiket tidak ditemukan. Periksa kembali NIK atau Nomor WhatsApp Anda.';
                }
            } catch (err) {
                console.error('Fetch error:', err);
                this.errorMessage = 'Terjadi gangguan jaringan. Pastikan koneksi internet ponsel Anda aktif dan coba lagi.';
            } finally {
                this.loading = false;
            }
        }
     }">

    <!-- Top Breadcrumb (Slide 5 Indicator) -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <a href="{{ route('home') }}" class="hover:text-emerald-700 transition-colors">Beranda</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 font-bold">Cek Status & Cari Tiket (Slide 5)</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-300 inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    Slide 5: Layanan Mandiri Pelacakan Tiket
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                    Cek Status & Cari Tiket Antrean
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Lupa nomor antrean atau ingin mengunduh ulang tiket? Temukan kembali bukti pendaftaran Anda di bawah ini.
                </p>
            </div>

            <!-- Action Link to Register -->
            <a href="{{ route('registration.create') }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-300 hover:border-emerald-500 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 text-xs font-bold rounded-xl transition-all shadow-2xs self-start sm:self-auto">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Daftar Antrean Baru</span>
            </a>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- BAGIAN 1: DATA TIKET AKTIF DARI PENYIMPANAN SEMENTARA (LOCALSTORAGE)-->
    <!-- Menampilkan kembali data tiket aktif jika ditemukan di perangkat  -->
    <!-- ================================================================= -->
    <div x-show="localTickets.length > 0" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-8">
        
        <div class="bg-gradient-to-br from-emerald-50 via-teal-50/50 to-white rounded-3xl border-2 border-emerald-300 p-5 sm:p-6 shadow-md relative overflow-hidden">
            <!-- Decorative badge watermark -->
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-emerald-200/30 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2">
                            <span>Tiket Aktif Ditemukan di Ponsel / Perangkat Ini</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        </h2>
                        <p class="text-xs text-slate-600">
                            Terdeteksi otomatis dari pendaftaran terakhir Anda di browser ini (Local Storage).
                        </p>
                    </div>
                </div>

                <!-- Clear Local Storage History Button -->
                <button type="button" 
                        @click="clearLocalHistory()" 
                        class="text-[11px] font-bold text-slate-500 hover:text-rose-600 hover:underline shrink-0 flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span class="hidden sm:inline">Hapus Riwayat</span>
                </button>
            </div>

            <!-- Carousel / List of Local Tickets -->
            <div class="space-y-3">
                <template x-for="(ticket, idx) in localTickets" :key="ticket.ticket_number">
                    <div class="bg-white rounded-2xl border border-emerald-200 p-4 sm:p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all hover:border-emerald-400">
                        <div class="space-y-1.5 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-mono font-black text-emerald-800 bg-emerald-100 px-3 py-1 rounded-xl border border-emerald-300">
                                    <span x-text="ticket.queue_number"></span>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700" 
                                      x-text="ticket.status_badge || 'Terdaftar'"></span>
                                <span class="text-xs text-slate-400 font-mono" x-text="ticket.ticket_number"></span>
                            </div>

                            <div class="text-xs sm:text-sm text-slate-800 font-extrabold" x-text="ticket.service_name"></div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-600">
                                <div>
                                    <span class="text-slate-400">Pemohon:</span> 
                                    <strong class="text-slate-900" x-text="ticket.full_name"></strong> 
                                    (<span class="font-mono" x-text="ticket.masked_nik || ticket.nik_masked"></span>)
                                </div>
                                <div>
                                    <span class="text-slate-400">Jadwal:</span> 
                                    <strong class="text-emerald-800" x-text="ticket.appointment_date"></strong> &bull; <span x-text="ticket.shift_name"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action for this local ticket -->
                        <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <a :href="'/tiket/' + ticket.ticket_number" 
                               class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Buka E-Tiket & QR</span>
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- BAGIAN 2: FORM PENCARIAN TIKET (NIK / WHATSAPP) - MOBILE FRIENDLY -->
    <!-- ================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-5 sm:p-8 space-y-6">
        
        <!-- Header Form -->
        <div class="text-center max-w-lg mx-auto">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h2 class="text-lg sm:text-xl font-black text-slate-900">
                Pencarian Tiket Pendaftaran Online
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Pilih metode pencarian di bawah menggunakan NIK atau Nomor WhatsApp yang Anda daftarkan.
            </p>
        </div>

        <!-- Segmented Tab Switcher (Mobile-Friendly Pill Control) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 p-1.5 bg-slate-100 rounded-2xl max-w-md mx-auto">
            
            <!-- Tab NIK -->
            <button type="button" 
                    @click="setTab('nik')" 
                    :class="searchTab === 'nik' ? 'bg-white text-emerald-800 font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                <span>Cari via NIK</span>
            </button>

            <!-- Tab WhatsApp -->
            <button type="button" 
                    @click="setTab('phone')" 
                    :class="searchTab === 'phone' ? 'bg-white text-emerald-800 font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="py-2.5 px-3 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                <span>No. WhatsApp</span>
            </button>

            <!-- Tab Kode Tiket (Opsional) -->
            <button type="button" 
                    @click="setTab('code')" 
                    :class="searchTab === 'code' ? 'bg-white text-emerald-800 font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="py-2.5 px-3 rounded-xl text-xs transition-all col-span-2 sm:col-span-1 flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Kode Tiket</span>
            </button>
        </div>

        <!-- Search Input Field Container -->
        <form @submit.prevent="doSearch()" class="max-w-xl mx-auto space-y-3">
            
            <div class="relative">
                <!-- Icon on Left -->
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <!-- Icon NIK -->
                    <template x-if="searchTab === 'nik'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    </template>
                    <!-- Icon Phone -->
                    <template x-if="searchTab === 'phone'">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    </template>
                    <!-- Icon Code -->
                    <template x-if="searchTab === 'code'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </template>
                </div>

                <!-- Input Input -->
                <input type="text" 
                       x-model="keyword"
                       :inputmode="searchTab === 'nik' || searchTab === 'phone' ? 'numeric' : 'text'"
                       :maxlength="searchTab === 'nik' ? 16 : 30"
                       :placeholder="searchTab === 'nik' ? 'Masukkan 16 digit NIK Anda...' : (searchTab === 'phone' ? 'Contoh: 081234567890...' : 'Contoh: CAPIL-CBR-A012 atau TKT-...')"
                       class="w-full pl-12 pr-12 py-4 rounded-2xl border-2 border-slate-300 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-500/20 text-slate-900 placeholder:text-slate-400 font-semibold text-sm sm:text-base transition-all shadow-inner">

                <!-- Clear button inside input -->
                <button type="button" 
                        x-show="keyword.length > 0" 
                        @click="keyword = ''; errorMessage = ''; hasSearched = false; searchResult = null" 
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Helpful Hints for Citizen -->
            <div class="flex items-center justify-between text-[11px] text-slate-500 px-1">
                <span x-show="searchTab === 'nik'">Wajib 16 digit angka sesuai KTP atau Kartu Keluarga.</span>
                <span x-show="searchTab === 'phone'">Nomor WhatsApp aktif yang diisi saat formulir antrean.</span>
                <span x-show="searchTab === 'code'">Nomor antrean unik atau kode booking resmi.</span>
                <span x-show="keyword.length > 0" class="font-mono text-slate-400" x-text="keyword.length + ' karakter'"></span>
            </div>

            <!-- Submit Button (Full Width, Mobile Friendly) -->
            <button type="submit" 
                    :disabled="loading" 
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-500 disabled:opacity-50 text-white font-extrabold text-sm sm:text-base shadow-lg shadow-emerald-700/25 transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                
                <span x-show="!loading" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari Tiket Saya Sekarang</span>
                </span>

                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Memeriksa Pangkalan Data Disdukcapil...</span>
                </span>
            </button>
        </form>

        <!-- Error Alert -->
        <div x-show="errorMessage" 
             x-cloak 
             x-transition 
             class="max-w-xl mx-auto p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs sm:text-sm text-rose-800 flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <strong class="font-bold block">Pencarian Tidak Ditemukan</strong>
                <p class="mt-0.5 leading-relaxed" x-text="errorMessage"></p>
                <div class="mt-2 text-xs text-rose-700">
                    Saran: Pastikan NIK atau Nomor WhatsApp sama persis dengan yang diisikan saat proses pendaftaran sebelumnya.
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- BAGIAN 3: HASIL PENCARIAN TIKET (LIVE RESULTS CARD)              -->
    <!-- ================================================================= -->
    <div x-show="hasSearched && allTickets.length > 0" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-8 space-y-5">
        
        <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                <span>Hasil Pencarian: Ditemukan</span>
                <span class="bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold" x-text="allTickets.length + ' Tiket'"></span>
            </h2>
            <span class="text-xs text-slate-500">Data Terverifikasi SIAK</span>
        </div>

        <!-- Tickets List -->
        <div class="space-y-5">
            <template x-for="(item, index) in allTickets" :key="item.ticket_number">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden transition-all hover:border-emerald-300">
                    
                    <!-- Card Top Header -->
                    <div class="bg-gradient-to-r from-emerald-800 to-slate-900 text-white p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3 border-b-2 border-amber-400">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/kabcirebon.png') }}" alt="Logo" class="w-8 h-10 object-contain drop-shadow-sm">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300 block">Disdukcapil Kab. Cirebon</span>
                                <h4 class="font-extrabold text-sm sm:text-base text-white" x-text="item.service_name"></h4>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider"
                                  :class="item.is_active ? 'bg-emerald-500/30 text-emerald-200 border border-emerald-400/40' : 'bg-slate-700 text-slate-300'"
                                  x-text="item.status_badge">
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 sm:p-6 space-y-5">
                        
                        <!-- Queue Number Box -->
                        <div class="bg-emerald-50/70 p-4 sm:p-5 rounded-2xl border-2 border-dashed border-emerald-300 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 block">NOMOR ANTREAN ANDA</span>
                                <div class="text-3xl sm:text-4xl font-black text-emerald-800 font-mono tracking-tight" x-text="item.queue_number"></div>
                                <span class="text-xs font-mono text-slate-500 font-semibold" x-text="'Kode: ' + item.ticket_number"></span>
                            </div>

                            <!-- Copy Button -->
                            <button type="button" 
                                    @click="copyText(item.queue_number, index)" 
                                    class="px-3 py-2 bg-white hover:bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-bold transition-all shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                <svg x-show="copiedIndex !== index" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <svg x-show="copiedIndex === index" class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="copiedIndex === index ? 'Tersalin!' : 'Salin Nomor'"></span>
                            </button>
                        </div>

                        <!-- Ticket Details Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs sm:text-sm">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-slate-400 text-xs block">Nama Pemohon:</span>
                                <strong class="text-slate-900 font-bold block" x-text="item.full_name"></strong>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-slate-400 text-xs block">NIK Pemohon (Privasi):</span>
                                <strong class="text-slate-900 font-bold font-mono block" x-text="item.nik_masked"></strong>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-slate-400 text-xs block">Tanggal Kunjungan:</span>
                                <strong class="text-emerald-800 font-bold block" x-text="item.appointment_date"></strong>
                            </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-slate-400 text-xs block">Sesi Shift:</span>
                                <strong class="text-slate-900 font-bold block" x-text="item.shift_name"></strong>
                            </div>

                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 sm:col-span-2">
                                <span class="text-amber-800 text-xs block font-bold">Waktu Wajib Tiba di Loket:</span>
                                <div class="text-amber-950 font-black text-sm">
                                    Pukul <span x-text="item.arrival_time"></span> (15 menit sebelum shift dibuka)
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons on Ticket Result -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200">
                            <div class="text-xs text-slate-500 text-center sm:text-left">
                                Tunjukkan tiket ini kepada petugas loket saat berkunjung.
                            </div>

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <a :href="'/tiket/' + item.ticket_number" 
                                   class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Buka Tiket & Unduh PDF (Slide 4)</span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </template>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- BAGIAN 4: PANDUAN CEPAT / FAQ WARGA                              -->
    <!-- ================================================================= -->
    <div class="mt-12 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-4">
        <h3 class="font-black text-sm sm:text-base text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Pertanyaan yang Sering Diajukan (FAQ)</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600 pt-2">
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                <strong class="text-slate-900 font-bold block">Bagaimana jika tiket saya belum sempat dicetak?</strong>
                <p>Cukup simpan tangkapan layar (screenshot) kode QR di ponsel Anda atau tunjukkan nomor antrean kepada petugas loket kedatangan.</p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                <strong class="text-slate-900 font-bold block">Apakah data NIK saya aman?</strong>
                <p>Ya, sistem menerapkan prinsip privasi UU PDP di mana sebagian digit NIK disensor pada tampilan umum.</p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                <strong class="text-slate-900 font-bold block">Bisa mencari dengan nomor WhatsApp yang berbeda?</strong>
                <p>Pencarian harus menggunakan nomor WhatsApp yang sama dengan yang diisi pada formulir pendaftaran.</p>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                <strong class="text-slate-900 font-bold block">Bagaimana jika lupa NIK dan nomor WhatsApp?</strong>
                <p>Silakan datang langsung ke loket informasi Disdukcapil Kabupaten Cirebon (Jl. Sunan Muria Komplek Pemda No. 04 Sumber) dengan membawa dokumen fisik asli.</p>
            </div>
        </div>
    </div>

</div>
@endsection
