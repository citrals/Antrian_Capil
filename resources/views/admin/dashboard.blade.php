@extends('layouts.app')

@section('title', 'Panel Admin Pengelola Antrean - Disdukcapil Kabupaten Cirebon')

@section('content')
@php
    $servicesDataJson = collect($serviceQuotas)->map(function($item) {
        return [
            'id' => $item['service']->id,
            'slug' => $item['service']->slug,
            'capacity' => $item['capacity'],
            'booked' => $item['booked'],
            'remaining' => $item['remaining'],
            'percent' => $item['percent'],
            'shifts' => collect($item['data']['shifts'])->map(function($sh) {
                return [
                    'id' => $sh['id'],
                    'name' => $sh['name'],
                    'time' => $sh['time'],
                    'total_quota' => $sh['total_quota'],
                    'booked_count' => $sh['booked_count'],
                    'remaining_quota' => $sh['remaining_quota'],
                    'is_full' => $sh['is_full'],
                    'percentage' => $sh['percentage'],
                    'status_label' => $sh['status_label'],
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp

<div class="bg-slate-100 min-h-screen pb-16" 
     x-data="{
        resetModalOpen: false,
        resetScope: 'all',
        confirmText: '',
        isResetting: false,
        toastVisible: false,
        toastMessage: '',
        statusModalOpen: false,
        activeAppointment: null,
        newStatus: '',
        isUpdatingStatus: false,
        currentDate: '{{ $selectedDate->toDateString() }}',
        hasResetHappened: false,
        metrics: {
            totalSelectedDate: {{ $totalSelectedDate }},
            waitingCount: {{ $waitingCount }},
            servingCount: {{ $servingCount }},
            completedCount: {{ $completedCount }},
            totalBookedAll: {{ $totalBookedAll }},
            totalCapacityAll: {{ $totalCapacityAll }},
            remainingAll: {{ max(0, $totalCapacityAll - $totalBookedAll) }}
        },
        servicesData: @js($servicesDataJson),

        triggerReset() {
            if (this.confirmText.trim().toUpperCase() !== 'RESET') {
                alert('Silakan ketik kata RESET untuk mengonfirmasi tindakan ini.');
                return;
            }

            this.isResetting = true;

            fetch('{{ route('admin.reset') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    scope: this.resetScope,
                    date: this.currentDate
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isResetting = false;
                this.resetModalOpen = false;
                this.confirmText = '';

                // 1. Seketika perbarui seluruh metrik & pendaftar ke angka 0 (Real-Time di tampilan)
                this.hasResetHappened = true;
                this.metrics.totalSelectedDate = 0;
                this.metrics.waitingCount = 0;
                this.metrics.servingCount = 0;
                this.metrics.completedCount = 0;
                this.metrics.totalBookedAll = 0;
                this.metrics.remainingAll = this.metrics.totalCapacityAll;

                // 2. Pulihkan sisa kuota ke kapasitas maksimal awal (penuh kembali 100%) untuk Shift Pagi & Shift Siang pada ke-4 kartu layanan
                if (this.servicesData && Array.isArray(this.servicesData)) {
                    this.servicesData.forEach(srv => {
                        srv.booked = 0;
                        srv.remaining = srv.capacity;
                        srv.percent = 0;
                        if (srv.shifts && Array.isArray(srv.shifts)) {
                            srv.shifts.forEach(sh => {
                                sh.booked_count = 0;
                                sh.remaining_quota = sh.total_quota;
                                sh.is_full = false;
                                sh.percentage = 0;
                                sh.status_label = 'Sisa Kuota: ' + sh.total_quota + ' / ' + sh.total_quota;
                            });
                        }
                    });
                }

                // Sinkronkan data jika respon API menyediakan objek terkalkulasi
                if (data.metrics) {
                    this.metrics.totalSelectedDate = data.metrics.totalSelectedDate;
                    this.metrics.waitingCount = data.metrics.waitingCount;
                    this.metrics.servingCount = data.metrics.servingCount;
                    this.metrics.completedCount = data.metrics.completedCount;
                    this.metrics.totalBookedAll = data.metrics.totalBookedAll;
                    this.metrics.totalCapacityAll = data.metrics.totalCapacityAll;
                    this.metrics.remainingAll = data.metrics.remainingCapacityAll;
                }
                if (data.serviceQuotas && Array.isArray(data.serviceQuotas)) {
                    this.servicesData = data.serviceQuotas;
                }

                // 3. Bersihkan status tiket yang tersimpan pada localStorage
                try {
                    localStorage.removeItem('capil_last_ticket');
                    localStorage.removeItem('capil_recent_search');
                    localStorage.removeItem('capil_ticket_cache');
                    window.dispatchEvent(new CustomEvent('capil-system-reset', { detail: data }));
                } catch (e) {
                    console.error('Gagal membersihkan localStorage:', e);
                }

                // 4. Tampilkan notifikasi toast berhasil
                this.showToast(data.message || 'Sistem antrean berhasil direset: 0 pendaftar aktif, sisa kuota seluruh layanan kembali ke kapasitas maksimal (100%).');

                // 5. Reload halaman setelah sejenak agar state server dan query pagination tetap konsisten
                setTimeout(() => {
                    window.location.reload();
                }, 1400);
            })
            .catch(err => {
                this.isResetting = false;
                alert('Terjadi kesalahan saat memproses reset sistem. Silakan coba lagi.');
            });
        },

        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => {
                this.toastVisible = false;
            }, 6000);
        },

        openStatusModal(apt) {
            this.activeAppointment = apt;
            this.newStatus = apt.status;
            this.statusModalOpen = true;
        },

        updateAppointmentStatus() {
            if (!this.activeAppointment || !this.newStatus) return;

            this.isUpdatingStatus = true;
            const url = '{{ url('admin/appointments') }}/' + this.activeAppointment.id + '/status';

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    status: this.newStatus
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isUpdatingStatus = false;
                this.statusModalOpen = false;
                this.showToast(data.message || 'Status antrean berhasil diperbarui.');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(err => {
                this.isUpdatingStatus = false;
                alert('Gagal memperbarui status antrean.');
            });
        }
     }"
     x-init="
        // Cek jika ada flash session reset dari form submit tradisional
        @if(session('system_reset'))
            try {
                localStorage.removeItem('capil_last_ticket');
                localStorage.removeItem('capil_recent_search');
                localStorage.removeItem('capil_ticket_cache');
            } catch(e) {}
            showToast('{{ session('success') }}');
        @elseif(session('success'))
            showToast('{{ session('success') }}');
        @endif
     ">

    <!-- Toast Notification (Floating Top-Right) -->
    <div x-show="toastVisible" 
         x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
         class="fixed top-24 right-4 sm:right-8 z-50 max-w-md w-full bg-emerald-900/95 text-white rounded-2xl shadow-2xl border-2 border-emerald-400/60 p-4 backdrop-blur-md flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 border border-emerald-400/40 mt-0.5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="grow pr-2">
            <h5 class="text-sm font-bold text-emerald-200 uppercase tracking-wider flex items-center gap-1.5">
                <span>Notifikasi Sistem</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            </h5>
            <p class="text-xs text-white mt-1 leading-relaxed font-medium" x-text="toastMessage"></p>
        </div>
        <button type="button" @click="toastVisible = false" class="text-emerald-300 hover:text-white p-1 rounded-lg hover:bg-emerald-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Admin Sub-Header / Control Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white border-b border-slate-700/80 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-16 bg-white/10 rounded-2xl p-1.5 backdrop-blur-xs flex items-center justify-center border border-white/20 shrink-0 shadow-inner">
                        <img src="{{ asset('images/kabcirebon.png') }}" alt="Logo Kab Cirebon" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/30 text-emerald-300 border border-emerald-400/40">
                                Panel Administrator
                            </span>
                            <span class="text-xs text-slate-300 font-mono hidden sm:inline">&bull; Modul Manajemen Antrean & Kuota</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-1">
                            Disdukcapil Kabupaten Cirebon
                        </h1>
                        <p class="text-xs text-slate-300 mt-0.5">
                            Pusat Kendali Operasional, Monitoring Kuota Real-Time & Reset Sistem Terpadu
                        </p>
                    </div>
                </div>

                <!-- Action Controls (Filter Tanggal & Tombol Reset Data) -->
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Halaman Publik</span>
                    </a>

                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-600 transition-colors">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Segarkan Data</span>
                    </a>

                    <!-- TOMBOL LOGOUT ADMIN -->
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Apakah Anda yakin ingin keluar dari Panel Administrator?')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-rose-300 hover:text-rose-100 text-xs font-bold border border-slate-600 transition-colors"
                                title="Keluar dari Panel Admin">
                            <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Logout</span>
                        </button>
                    </form>

                    <!-- TOMBOL UTAMA: RESET DATA SISTEM -->
                    <button type="button" 
                            @click="resetModalOpen = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white text-xs font-extrabold shadow-lg shadow-rose-900/30 hover:shadow-rose-900/50 transition-all transform hover:-translate-y-0.5 border border-rose-400/40">
                        <svg class="w-4 h-4 text-rose-100 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>RESET DATA SISTEM</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        <!-- Banner Info & Danger Action Box -->
        <div class="bg-gradient-to-br from-rose-50 via-white to-red-50 rounded-3xl border-2 border-rose-200/80 p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 border border-rose-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-rose-200 text-rose-900 px-2 py-0.5 rounded-md">Pusat Kendali Reset</span>
                        <span class="text-xs text-rose-700 font-semibold">Tindakan Tingkat Lanjut</span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">
                        Reset Antrean & Pemulihan Kuota Harian Menyeluruh
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed max-w-3xl">
                        Fungsi ini akan <strong>mengosongkan seluruh antrean aktif</strong>, mengembalikan urutan nomor antrean ke <strong>posisi awal (0)</strong>, memulihkan kuota harian layanan ke <strong>kapasitas 100%</strong>, dan <strong>membersihkan tiket di localStorage browser</strong>.
                    </p>
                </div>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <button type="button" 
                        @click="resetModalOpen = true"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Buka Dialog Reset Data</span>
                </button>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-5">
            <!-- Total Antrean Hari Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Antrean Hari Ini</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2 font-mono" x-text="metrics.totalSelectedDate">
                    {{ $totalSelectedDate }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Total pendaftar tanggal ini</p>
            </div>

            <!-- Menunggu / Hadir -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">Menunggu Loket</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-amber-800 mt-2 font-mono" x-text="metrics.waitingCount">
                    {{ $waitingCount }}
                </div>
                <p class="text-[11px] text-amber-600 mt-1">Status confirmed / check-in</p>
            </div>

            <!-- Sedang Diproses -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-teal-700 uppercase tracking-wider">Sedang Dilayani</span>
                    <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-teal-800 mt-2 font-mono" x-text="metrics.servingCount">
                    {{ $servingCount }}
                </div>
                <p class="text-[11px] text-teal-600 mt-1">Sedang berada di loket</p>
            </div>

            <!-- Selesai Dilayani -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Selesai</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-emerald-800 mt-2 font-mono" x-text="metrics.completedCount">
                    {{ $completedCount }}
                </div>
                <p class="text-[11px] text-emerald-600 mt-1">Berkas tuntas diproses</p>
            </div>

            <!-- Kapasitas Kuota Real-Time -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-purple-700 uppercase tracking-wider">Kuota Terpakai</span>
                    <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-purple-900 mt-2 font-mono">
                    <span x-text="metrics.totalBookedAll">{{ $totalBookedAll }}</span> / <span class="text-slate-400 text-lg" x-text="metrics.totalCapacityAll">{{ $totalCapacityAll }}</span>
                </div>
                <p class="text-[11px] text-purple-700 mt-1">Sisa Kuota: <span x-text="metrics.remainingAll">{{ max(0, $totalCapacityAll - $totalBookedAll) }}</span> slot</p>
            </div>
        </div>

        <!-- Section 1: Monitoring Kuota per Layanan Real-Time -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span>Monitoring Kuota Layanan Real-Time</span>
                        <button type="button" 
                                onclick="openMonitoringDatePicker()" 
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-200 shadow-2xs inline-flex items-center gap-1 cursor-pointer transition-colors"
                                title="Klik untuk mengganti tanggal monitoring">
                            <span>{{ $selectedDate->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan kuota maksimal dan sisa slot per shift yang diperbarui secara langsung
                    </p>
                </div>

                <!-- Form Filter Tanggal (Auto-Submit saat tanggal dipilih & Tombol Aksi) -->
                <form action="{{ route('admin.dashboard', [], false) }}" method="GET" class="flex flex-wrap items-center gap-2" id="monitoringDateFilterForm">
                    @if(request('service_id'))
                        <input type="hidden" name="service_id" value="{{ request('service_id') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    @if(!$selectedDate->isToday())
                        <a href="{{ route('admin.dashboard', ['date' => now()->toDateString()], false) }}" 
                           class="px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1 shadow-2xs"
                           title="Kembali ke jadwal hari ini">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Hari Ini</span>
                        </a>
                    @endif

                    <div class="relative flex items-center group cursor-pointer" onclick="openMonitoringDatePicker()">
                        <input type="date" 
                               name="date" 
                               id="adminMonitoringDate"
                               value="{{ $selectedDate->toDateString() }}" 
                               onclick="event.stopPropagation(); try { if (typeof this.showPicker === 'function') this.showPicker(); } catch(e) {}"
                               onchange="submitMonitoringDate(this)"
                               oninput="submitMonitoringDate(this)"
                               class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white cursor-pointer shadow-2xs">
                    </div>

                    <button type="button" 
                            id="btnPilihTanggal"
                            onclick="handlePilihTanggalAction()"
                            class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white rounded-xl text-xs font-bold transition-all shadow-2xs cursor-pointer inline-flex items-center gap-1.5"
                            title="Klik untuk memilih atau menerapkan tanggal monitoring">
                        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Pilih Tanggal</span>
                    </button>
                </form>
            </div>

            @php
                $customServiceTitles = [
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

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($serviceQuotas as $item)
                    @php
                        $cardIdx = $loop->index;
                        $srv = $item['service'];
                        $shifts = $item['data']['shifts'];
                        $cardTitle = $customServiceTitles[$srv->slug] ?? ($titleByOriginalName[$srv->name] ?? match ($loop->iteration) {
                            1 => 'PELAYAN ADMINDUK',
                            2 => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                            3 => 'KONSULTASI / PENGADUAN',
                            4 => 'LOKET PRIORITAS',
                            default => $srv->name,
                        });
                    @endphp
                    <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-4 space-y-3">
                        <!-- Header Kotak Kartu Layanan (Rata Tengah & Ukuran Pas Sesuai Permintaan) -->
                        <div class="pb-3 border-b border-slate-200">
                            <!-- Info Status & Kuota Persentase -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                                    Layanan #0{{ $loop->iteration }}
                                </span>
                                <span class="text-xs font-black font-mono px-2 py-0.5 rounded-md transition-colors"
                                      :class="(servicesData[{{ $cardIdx }}]?.percent ?? {{ $item['percent'] }}) >= 100 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                      x-text="(servicesData[{{ $cardIdx }}]?.percent ?? {{ $item['percent'] }}) + '% Terisi'">
                                    {{ $item['percent'] }}% Terisi
                                </span>
                            </div>

                            <!-- Kotak Header Judul Layanan -->
                            <div class="bg-white/90 border border-slate-200/80 rounded-xl px-3 py-2.5 min-h-[66px] flex flex-col items-center justify-center text-center shadow-2xs">
                                <h4 class="font-black text-slate-900 text-center uppercase tracking-tight {{ strlen($cardTitle) > 25 ? 'text-xs sm:text-[13px] leading-snug' : 'text-sm sm:text-base leading-tight' }}">
                                    {{ $cardTitle }}
                                </h4>
                                @if($srv->subtitle)
                                    <span class="text-[10.5px] text-slate-500 text-center block mt-1 leading-tight line-clamp-1">
                                        {{ $srv->subtitle }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Shifts Breakdown (Shift Pagi & Shift Siang) -->
                        <div class="space-y-2">
                            @foreach($shifts as $sh)
                                @php
                                    $shIdx = $loop->index;
                                @endphp
                                <div class="bg-white p-2.5 rounded-xl border border-slate-200/90 text-xs">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-bold text-slate-800">{{ $sh['name'] }}</span>
                                        <span class="font-mono font-semibold transition-colors"
                                              :class="(servicesData[{{ $cardIdx }}]?.shifts[{{ $shIdx }}]?.is_full) ? 'text-rose-600' : 'text-emerald-700'"
                                              x-text="'Sisa: ' + (servicesData[{{ $cardIdx }}]?.shifts[{{ $shIdx }}]?.remaining_quota ?? {{ $sh['remaining_quota'] }}) + ' / ' + (servicesData[{{ $cardIdx }}]?.shifts[{{ $shIdx }}]?.total_quota ?? {{ $sh['total_quota'] }})">
                                            Sisa: {{ $sh['remaining_quota'] }} / {{ $sh['total_quota'] }}
                                        </span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full transition-all duration-300"
                                             :class="(servicesData[{{ $cardIdx }}]?.shifts[{{ $shIdx }}]?.is_full) ? 'bg-rose-500' : 'bg-emerald-500'"
                                             :style="'width: ' + (servicesData[{{ $cardIdx }}]?.shifts[{{ $shIdx }}]?.percentage ?? {{ $sh['percentage'] }}) + '%'"
                                             style="width: {{ $sh['percentage'] }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                        <span>{{ $sh['time'] }}</span>
                                        <span x-text="(servicesData[{{ $cardIdx }}]?.shifts[{{ $shIdx }}]?.booked_count ?? {{ $sh['booked_count'] }}) + ' terdaftar'">
                                            {{ $sh['booked_count'] }} terdaftar
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section 2: Tabel Antrean Hari Ini & Operasional Loket -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex flex-wrap items-center gap-2">
                        <span>Daftar Antrean Terdaftar</span>
                        <button type="button" 
                                onclick="openTableDatePicker()" 
                                class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs inline-flex items-center gap-1.5 cursor-pointer transition-colors"
                                title="Klik untuk mengganti tanggal antrean">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Tanggal: {{ $selectedDate->format('d/m/Y') }}</span>
                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Lacak data pemohon, nomor unik, shift, dan perbarui status pelayanan
                    </p>
                </div>

                <!-- Search & Filters -->
                <form action="{{ route('admin.dashboard', [], false) }}" method="GET" class="flex flex-wrap items-center gap-2" id="tableFilterForm">
                    <!-- Date Input Filter -->
                    <div class="relative flex items-center group cursor-pointer" onclick="openTableDatePicker()">
                        <input type="date" 
                               name="date" 
                               id="tableFilterDate" 
                               value="{{ $selectedDate->toDateString() }}" 
                               onclick="event.stopPropagation(); try { if (typeof this.showPicker === 'function') this.showPicker(); } catch(e) {}"
                               onchange="this.form.submit()"
                               oninput="if(this.value.length === 10) this.form.submit()"
                               class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white cursor-pointer shadow-2xs"
                               title="Pilih tanggal antrean">
                    </div>
                    
                    <select name="service_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Layanan</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>
                                {{ $customServiceTitles[$s->slug] ?? ($titleByOriginalName[$s->name] ?? $s->name) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Status</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Menunggu</option>
                        <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Hadir</option>
                        <option value="serving" {{ request('status') == 'serving' ? 'selected' : '' }}>Diproses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Batal</option>
                    </select>

                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari NIK / Nama / No Antrean..." 
                               class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-medium pr-8 focus:ring-2 focus:ring-emerald-500">
                        @if(request('search'))
                            <a href="{{ route('admin.dashboard', ['date' => $selectedDate->toDateString()], false) }}" class="absolute right-2 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold">&times;</a>
                        @endif
                    </div>

                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nomor Antrean</th>
                            <th class="px-5 py-3.5">Kode Tiket</th>
                            <th class="px-5 py-3.5">Pemohon</th>
                            <th class="px-5 py-3.5">Layanan</th>
                            <th class="px-5 py-3.5">Shift</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-center">Aksi Petugas</th>
                        </tr>
                    </thead>
                    <tbody x-show="!hasResetHappened" class="divide-y divide-slate-100">
                        @forelse($appointments as $apt)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-4 font-mono font-black text-sm text-emerald-800">
                                    {{ $apt->queue_number }}
                                </td>
                                <td class="px-5 py-4 font-mono font-semibold text-slate-700">
                                    <a href="{{ route('ticket.show', ['ticketNumber' => $apt->ticket_number]) }}" target="_blank" class="hover:underline text-emerald-700 font-bold">
                                        {{ $apt->ticket_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">{{ $apt->full_name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">NIK: {{ $apt->masked_nik }} &bull; {{ $apt->phone }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 truncate max-w-xs">{{ $apt->address }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-bold text-slate-800">{{ $customServiceTitles[$apt->service->slug] ?? ($titleByOriginalName[$apt->service->name] ?? $apt->service->name) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 capitalize">
                                        Shift {{ $apt->shift }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $badgeClass = match($apt->status) {
                                            'confirmed' => 'bg-amber-100 text-amber-800 border-amber-300',
                                            'checked_in' => 'bg-blue-100 text-blue-800 border-blue-300',
                                            'serving' => 'bg-teal-100 text-teal-800 border-teal-300 animate-pulse',
                                            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-300',
                                            default => 'bg-slate-100 text-slate-800 border-slate-300',
                                        };
                                        $statusText = match($apt->status) {
                                            'confirmed' => 'Menunggu',
                                            'checked_in' => 'Hadir di Loket',
                                            'serving' => 'Sedang Diproses',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default => $apt->status,
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <button type="button" 
                                            @click="openStatusModal({ id: {{ $apt->id }}, queue_number: '{{ $apt->queue_number }}', name: '{{ addslashes($apt->full_name) }}', status: '{{ $apt->status }}' })"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition-colors">
                                        <span>Kelola</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                    </div>
                                    <p class="font-bold text-slate-700 text-sm">Tidak ada data antrean terdaftar</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada pemohon yang terdaftar pada kriteria tanggal atau filter ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tbody x-show="hasResetHappened" x-cloak class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500 bg-emerald-50/20">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center mb-3 font-mono font-black text-xl border border-emerald-300">
                                    0
                                </div>
                                <p class="font-extrabold text-slate-800 text-sm">Sistem Berhasil Direset ke Posisi Awal (0 Antrean)</p>
                                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                                    Seluruh pendaftar telah dikosongkan. Sisa kuota untuk Shift Pagi dan Shift Siang telah kembali penuh (100%) dan nomor antrean berikutnya akan dimulai dari 001.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($appointments->hasPages())
                <div class="p-4 border-t border-slate-200" x-show="!hasResetHappened">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI RESET SISTEM (POP-UP DIALOG UNTUK MENCEGAH SALAH KLIK)  -->
    <!-- ========================================================================= -->
    <div x-show="resetModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="if (!isResetting) resetModalOpen = false">
        
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-rose-300 transform transition-all"
             @click.outside="if (!isResetting) resetModalOpen = false">
            
            <!-- Modal Header Merah Bahaya -->
            <div class="bg-gradient-to-r from-red-600 via-rose-600 to-red-800 p-6 text-white relative">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/15 border border-white/30 flex items-center justify-center text-white shrink-0">
                        <svg class="w-7 h-7 text-amber-200 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-rose-200 bg-red-950/40 px-2 py-0.5 rounded">Tindakan Kritis</span>
                        <h3 class="text-lg font-black leading-tight text-white mt-0.5">Konfirmasi Reset Sistem Antrean</h3>
                    </div>
                </div>

                <button type="button" 
                        :disabled="isResetting"
                        @click="resetModalOpen = false" 
                        class="absolute top-5 right-5 text-rose-200 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Content & Warning -->
            <div class="p-6 space-y-4">
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl space-y-2 text-xs text-rose-900 leading-relaxed">
                    <p class="font-bold flex items-center gap-1.5 text-rose-800">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>Perhatian: Dampak Reset Sistem Menyeluruh</span>
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 pl-1 font-medium">
                        <li><strong>Daftar antrean aktif akan dikosongkan</strong> secara total.</li>
                        <li><strong>Penomoran antrean dikembalikan ke awal (0)</strong>; pendaftar berikutnya akan memperoleh nomor urut 001.</li>
                        <li><strong>Kuota harian seluruh layanan dipulihkan ke 100%</strong> kapasitas maksimal secara real-time.</li>
                        <li><strong>Status tiket tersimpan di localStorage browser akan dibersihkan</strong>.</li>
                    </ul>
                </div>

                <!-- Pilihan Scope / Cakupan Reset -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Cakupan Reset:
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                               :class="resetScope === 'all' ? 'border-red-500 bg-red-50/50 ring-2 ring-red-500/20' : 'border-slate-200 hover:bg-slate-50'">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="scope" value="all" x-model="resetScope" class="text-red-600 focus:ring-red-500">
                                <span class="font-bold text-xs text-slate-900">Total Keseluruhan</span>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-1">Kosongkan semua data antrean & kuota</span>
                        </label>

                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                               :class="resetScope === 'today' ? 'border-red-500 bg-red-50/50 ring-2 ring-red-500/20' : 'border-slate-200 hover:bg-slate-50'">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="scope" value="today" x-model="resetScope" class="text-red-600 focus:ring-red-500">
                                <span class="font-bold text-xs text-slate-900">Reset Harian</span>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-1">Kosongkan antrean tanggal {{ $selectedDate->format('d/m/Y') }} & hari ini</span>
                        </label>
                    </div>
                </div>

                <!-- Konfirmasi Ketik 'RESET' untuk Mencegah Salah Klik -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Ketik kata <span class="font-mono bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-black tracking-wider">RESET</span> di bawah ini untuk konfirmasi:
                    </label>
                    <input type="text" 
                           x-model="confirmText" 
                           @keydown.enter.prevent="if (confirmText.trim().toUpperCase() === 'RESET') triggerReset()"
                           placeholder="Ketik RESET di sini..." 
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm font-bold text-slate-900 uppercase font-mono tracking-widest text-center">
                    <p class="text-[11px] text-slate-500 mt-1 text-center">
                        Tombol eksekusi hanya akan aktif jika Anda mengetik kata RESET dengan benar.
                    </p>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div class="bg-slate-50 p-5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="button" 
                        :disabled="isResetting"
                        @click="resetModalOpen = false" 
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors">
                    Batalkan
                </button>

                <button type="button" 
                        @click="triggerReset()"
                        :disabled="confirmText.trim().toUpperCase() !== 'RESET' || isResetting"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-red-600/30 transition-all">
                    <span x-show="!isResetting" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Ya, Eksekusi Reset Sekarang</span>
                    </span>
                    <span x-show="isResetting" x-cloak class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Mereset Sistem...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL UPDATE STATUS ANTREAN                                               -->
    <!-- ========================================================================= -->
    <div x-show="statusModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="statusModalOpen = false">
        
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden border border-slate-200"
             @click.outside="statusModalOpen = false">
            
            <div class="bg-slate-900 p-4 text-white flex justify-between items-center">
                <div>
                    <h4 class="font-bold text-sm">Kelola Status Antrean</h4>
                    <p class="text-xs text-slate-300 font-mono mt-0.5" x-text="activeAppointment?.queue_number"></p>
                </div>
                <button type="button" @click="statusModalOpen = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <div class="p-5 space-y-4">
                <div>
                    <span class="text-xs text-slate-500">Nama Pemohon:</span>
                    <p class="font-bold text-slate-900 text-sm" x-text="activeAppointment?.name"></p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Status Baru:
                    </label>
                    <select x-model="newStatus" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-emerald-500">
                        <option value="confirmed">Menunggu (Confirmed)</option>
                        <option value="checked_in">Hadir di Loket (Checked In)</option>
                        <option value="serving">Sedang Diproses (Serving)</option>
                        <option value="completed">Selesai Dilayani (Completed)</option>
                        <option value="cancelled">Dibatalkan (Cancelled)</option>
                    </select>
                </div>
            </div>

            <div class="bg-slate-50 px-5 py-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" @click="statusModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">
                    Batal
                </button>
                <button type="button" 
                        @click="updateAppointmentStatus()"
                        :disabled="isUpdatingStatus"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors">
                    <span x-show="!isUpdatingStatus">Simpan Status</span>
                    <span x-show="isUpdatingStatus" x-cloak>Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isSubmittingMonitoringDate = false;

    function openMonitoringDatePicker() {
        const input = document.getElementById('adminMonitoringDate');
        if (!input) return;
        try {
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.focus();
            }
        } catch (e) {
            input.focus();
        }
    }

    function submitMonitoringDate(input) {
        if (!input || !input.value) return;
        // Format YYYY-MM-DD has 10 characters
        if (input.value.length === 10 && !isSubmittingMonitoringDate) {
            isSubmittingMonitoringDate = true;
            const form = document.getElementById('monitoringDateFilterForm');
            if (form) {
                form.submit();
            }
        }
    }

    function handlePilihTanggalAction() {
        const input = document.getElementById('adminMonitoringDate');
        if (!input) return;
        const currentDate = '{{ $selectedDate->toDateString() }}';

        // Jika user sudah memilih atau mengetik tanggal baru di input, langsung kirim form
        if (input.value && input.value !== currentDate && input.value.length === 10) {
            if (!isSubmittingMonitoringDate) {
                isSubmittingMonitoringDate = true;
                const form = document.getElementById('monitoringDateFilterForm');
                if (form) {
                    form.submit();
                }
            }
        } else {
            // Jika tanggal masih sama atau belum dipilih, buka kalender pemilih tanggal
            openMonitoringDatePicker();
        }
    }

    function openTableDatePicker() {
        const input = document.getElementById('tableFilterDate');
        if (!input) return;
        try {
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.focus();
            }
        } catch (e) {
            input.focus();
        }
    }
</script>
@endpush
