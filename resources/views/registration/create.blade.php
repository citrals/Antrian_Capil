@extends('layouts.app')

@section('title', 'Pendaftaran Antrean Online - Disdukcapil Kabupaten Cirebon')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
     x-data="registrationWizard({
        services: {{ Js::from($services) }},
        selectedServiceId: {{ $selectedService->id }},
        holidaysMap: {{ Js::from($holidays) }},
        initialDate: '{{ $initialDate->toDateString() }}',
        currentYear: {{ $initialDate->year }},
        currentMonth: {{ $initialDate->month - 1 }},
        shiftsData: {{ Js::from($initialShifts['shifts']) }}
     })">

    <!-- Top Breadcrumb & Title -->
    <div class="mb-8">
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <a href="{{ route('home') }}" class="hover:text-emerald-700 transition-colors">Beranda</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 font-bold">Pendaftaran Antrean Online</span>
        </nav>
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Pilih Layanan, Tanggal & Sesi Waktu
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Lengkapi pemilihan formulir di bawah ini untuk mengamankan nomor antrean dan kuota loket Disdukcapil Cirebon.
                </p>
            </div>

            <!-- Steps indicator pill -->
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-2xs self-start sm:self-auto text-xs font-bold text-slate-600">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[11px]">1</span>
                <span>Layanan</span>
                <span class="text-slate-300">&bull;</span>
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[11px]">2</span>
                <span>Jadwal & Shift</span>
                <span class="text-slate-300">&bull;</span>
                <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[11px]">3</span>
                <span>Data Diri</span>
            </div>
        </div>
    </div>

    <!-- Alert / Validation Feedback -->
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1">
            <div class="font-bold flex items-center gap-2 text-rose-900">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Mohon periksa kembali formulir pendaftaran:</span>
            </div>
            <ul class="list-disc list-inside pl-7 space-y-0.5 text-xs text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('registration.form') }}" method="GET" id="bookingForm" class="space-y-6">

        <!-- Input Tersembunyi Sinkronisasi Form -->
        <input type="hidden" name="service_id" :value="selectedServiceId">
        <input type="hidden" name="appointment_date" :value="selectedDate">
        <input type="hidden" name="shift" :value="selectedShift">

        <!-- 1. BAGIAN: PILIHAN LAYANAN KEPENDUDUKAN (SLIDE 2) -->
        <section class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">1</span>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Pilihan Layanan Kependudukan</h2>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5 pl-8">
                        Pilih salah satu jenis dokumen kependudukan yang ingin Anda proses di loket dinas.
                    </p>
                </div>

                <!-- Dropdown Selector (Syncs with Cards) -->
                <div class="sm:w-64">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1 sm:hidden">
                        Pilih Lewat Dropdown:
                    </label>
                    <select x-model="selectedServiceId" 
                            @change="onServiceChange()"
                            class="w-full text-xs font-semibold py-1.5 px-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        <template x-for="service in services" :key="service.id">
                            <option :value="service.id" x-text="getServiceName(service)"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Card Pilihan Jenis Layanan (Card Selector) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <template x-for="service in services" :key="service.id">
                    <div @click="selectService(service.id)"
                         :class="selectedServiceId == service.id 
                            ? 'border-emerald-600 bg-emerald-50/50 ring-2 ring-emerald-500/20 shadow-sm' 
                            : 'border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/50'"
                         class="cursor-pointer rounded-xl p-3 border transition-all duration-200 flex flex-col justify-between relative group">
                        
                        <!-- Checked indicator badge -->
                        <div x-show="selectedServiceId == service.id" 
                             class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>

                        <div>
                            <!-- Header Bar: Icon & Badge -->
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base transition-colors"
                                     :class="selectedServiceId == service.id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'">
                                    <template x-if="service.slug === 'ktp-el'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                    </template>
                                    <template x-if="service.slug === 'kartu-keluarga'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </template>
                                    <template x-if="service.slug === 'akta-kelahiran-kematian'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </template>
                                    <template x-if="service.slug === 'pindah-datang'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    </template>
                                </div>
                                <span class="text-[8.5px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded-full border border-emerald-200/60" x-text="service.badge || 'Resmi'"></span>
                            </div>

                            <!-- Kotak Header Judul Kartu (Rata Tengah & Ukuran Pas Sesuai Permintaan) -->
                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl px-2 py-1.5 min-h-[48px] flex flex-col items-center justify-center text-center mb-1.5 shadow-2xs group-hover:border-emerald-300 transition-colors w-full">
                                <h3 class="font-black text-slate-900 text-center uppercase tracking-tight"
                                    :class="getServiceName(service).length > 22 ? 'text-[10px] sm:text-[10.5px] leading-tight' : 'text-[11px] sm:text-xs leading-tight'"
                                    x-text="getServiceName(service)"></h3>
                            </div>

                            <p class="text-[10.5px] text-slate-500 mt-0.5 line-clamp-2 text-center leading-snug" x-text="service.description"></p>
                        </div>

                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500">
                            <span>Durasi Loket:</span>
                            <span class="font-bold text-slate-700" x-text="'&plusmn; ' + service.estimated_minutes + ' menit'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- 2. BAGIAN: SISTEM KALENDER INTERAKTIF & VALIDASI HARI LIBUR (SLIDE 2) -->
        <section class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">2</span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Sistem Kalender Interaktif & Validasi Hari Libur</h2>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5 pl-8">
                    Sistem otomatis mendeteksi hari libur nasional atau akhir pekan (Sabtu-Minggu), memblokirnya agar tidak bisa dipilih, dan menampilkan label teks dengan jelas bertuliskan <span class="font-bold text-rose-600 bg-rose-50 px-1 py-0.5 rounded border border-rose-200">"LIBUR"</span> pada tanggal tersebut.
                </p>
            </div>

            <!-- Centered & Compact Calendar Wrapper -->
            <div class="max-w-2xl mx-auto space-y-3.5">
                <!-- Legend Status Hari -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 sm:gap-4 text-[11px] bg-slate-50 p-2.5 sm:p-3 rounded-xl border border-slate-200/80">
                    <span class="font-bold text-slate-700">Keterangan:</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-white border-2 border-emerald-600"></span>
                        <span class="text-slate-600">Hari Kerja (Buka)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-emerald-600"></span>
                        <span class="text-slate-600 font-bold text-emerald-800">Dipilih</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-rose-100 border border-rose-300"></span>
                        <span class="text-rose-700 font-bold">Libur / Akhir Pekan</span>
                    </div>
                </div>

                <!-- Calendar Container (Compact & Proporsional) -->
                <div class="border border-slate-200 rounded-2xl p-3 sm:p-4 bg-slate-50/50 shadow-xs">
                    <!-- Calendar Month Navigation Header -->
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900" x-text="monthNames[currentMonth] + ' ' + currentYear"></h3>
                            <p class="text-[10px] sm:text-[11px] text-slate-500">Klik tanggal hari kerja aktif untuk memilih jadwal</p>
                        </div>

                        <div class="flex items-center gap-1">
                            <button type="button" 
                                    @click="prevMonth()"
                                    class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-colors shadow-2xs cursor-pointer"
                                    title="Bulan Sebelumnya">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" 
                                    @click="nextMonth()"
                                    class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-colors shadow-2xs cursor-pointer"
                                    title="Bulan Berikutnya">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Days of Week Headers (Senin - Minggu) -->
                    <div class="grid grid-cols-7 gap-1 sm:gap-1.5 text-center text-[10px] sm:text-[11px] font-semibold mb-1">
                        <div class="py-1 text-slate-700">Sen</div>
                        <div class="py-1 text-slate-700">Sel</div>
                        <div class="py-1 text-slate-700">Rab</div>
                        <div class="py-1 text-slate-700">Kam</div>
                        <div class="py-1 text-slate-700">Jum</div>
                        <div class="py-1 text-rose-600 bg-rose-50 rounded">Sab</div>
                        <div class="py-1 text-rose-600 bg-rose-50 rounded">Min</div>
                    </div>

                    <!-- Calendar Days Grid (Proporsional & Ringkas) -->
                    <div class="grid grid-cols-7 gap-1 sm:gap-1.5">
                        <!-- Blank padding cells -->
                        <template x-for="blank in blankDays" :key="'blank-' + blank">
                            <div class="h-11 sm:h-12 rounded-lg bg-transparent"></div>
                        </template>

                        <!-- Actual Month Days -->
                        <template x-for="dayObj in monthDays" :key="dayObj.dateString">
                            <div @click="!dayObj.isHoliday && !dayObj.isPast && selectDate(dayObj.dateString)"
                                 :title="dayObj.isHoliday ? (dayObj.holidayName || 'Libur') : (dayObj.isPast ? 'Tanggal telah lewat' : 'Pilih ' + dayObj.dateString)"
                                 :class="{
                                     'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-600/30 ring-2 ring-emerald-500/30 scale-[1.02]': selectedDate === dayObj.dateString && !dayObj.isHoliday,
                                     'bg-white border-slate-200 hover:border-emerald-400 hover:shadow-xs cursor-pointer text-slate-800': selectedDate !== dayObj.dateString && !dayObj.isHoliday && !dayObj.isPast,
                                     'bg-rose-50/70 border-rose-200/80 cursor-not-allowed opacity-90': dayObj.isHoliday,
                                     'bg-slate-100/60 border-slate-200 cursor-not-allowed opacity-50': dayObj.isPast && !dayObj.isHoliday
                                 }"
                                 class="h-11 sm:h-12 p-1 rounded-lg border flex flex-col justify-between transition-all duration-150 relative group">
                                
                                <!-- Date Number & Today marker -->
                                <div class="flex items-center justify-between leading-none">
                                    <span class="text-[11px] font-bold"
                                          :class="{
                                              'text-white': selectedDate === dayObj.dateString && !dayObj.isHoliday,
                                              'text-rose-600': dayObj.isHoliday,
                                              'text-slate-800': !dayObj.isHoliday && selectedDate !== dayObj.dateString
                                          }"
                                          x-text="dayObj.dayNumber">
                                    </span>

                                    <template x-if="dayObj.isToday">
                                        <span class="w-1.5 h-1.5 rounded-full" 
                                              :class="selectedDate === dayObj.dateString ? 'bg-white' : 'bg-emerald-500'"></span>
                                    </template>
                                </div>

                                <!-- LABEL STATUS JELAS BERTULISKAN 'LIBUR' (Slide 2 Requirement) -->
                                <template x-if="dayObj.isHoliday">
                                    <div class="mt-auto">
                                        <span class="inline-block w-full py-0.5 rounded bg-rose-600 text-white font-extrabold text-[8px] text-center tracking-wider uppercase leading-none shadow-2xs truncate">
                                            LIBUR
                                        </span>
                                    </div>
                                </template>

                                <!-- Available date marker -->
                                <template x-if="!dayObj.isHoliday && !dayObj.isPast">
                                    <div class="mt-auto flex items-center justify-between text-[8px] leading-none">
                                        <span :class="selectedDate === dayObj.dateString ? 'text-emerald-100 font-semibold' : 'text-emerald-700 font-bold'">
                                            Buka
                                        </span>
                                        <span :class="selectedDate === dayObj.dateString ? 'text-white' : 'text-slate-400'">
                                            &bull; 2 Sesi
                                        </span>
                                    </div>
                                </template>

                                <!-- Past date marker -->
                                <template x-if="dayObj.isPast && !dayObj.isHoliday">
                                    <div class="mt-auto text-[8px] text-slate-400 font-medium leading-none">
                                        Lewat
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Selected Date Feedback banner -->
                <div class="p-2.5 sm:p-3 rounded-xl bg-emerald-50/90 border border-emerald-200/90 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <span class="text-slate-500 text-[10px] block leading-tight">Tanggal Kunjungan Terpilih:</span>
                            <strong class="text-emerald-950 font-bold text-xs" x-text="formattedSelectedDate"></strong>
                        </div>
                    </div>

                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200/80 text-emerald-800 border border-emerald-300/60 self-start sm:self-auto leading-tight">
                        Hari Kerja Terverifikasi
                    </span>
                </div>
            </div>
        </section>

        <!-- 3. BAGIAN: MANAJEMEN SHIFT & KUOTA REAL-TIME (SLIDE 2) -->
        <section class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">3</span>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Manajemen Shift & Kuota Real-Time</h2>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5 pl-8">
                        Pilih sesi waktu kedatangan. Informasi sisa kuota diperbarui secara real-time. Jika kuota habis, shift otomatis dinonaktifkan dengan label <span class="font-bold text-rose-600 bg-rose-50 px-1 py-0.5 rounded border border-rose-200">"Kuota Penuh"</span>.
                    </p>
                </div>

                <!-- Loading indicator for real-time fetch -->
                <div x-show="loadingShifts" x-cloak class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Memperbarui Kuota...</span>
                </div>
            </div>

            <!-- Pilihan Sesi Waktu / Shift Cards (Pagi, Siang) - Ukuran Ringkas & Rapi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                <template x-for="shift in shifts" :key="shift.id">
                    <div @click="!shift.is_disabled && selectShift(shift.id)"
                         :class="{
                             'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-500/20 shadow-sm': selectedShift === shift.id && !shift.is_disabled,
                             'border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/50 cursor-pointer': selectedShift !== shift.id && !shift.is_disabled,
                             'border-slate-200 bg-slate-100/70 cursor-not-allowed opacity-60': shift.is_disabled
                         }"
                         class="rounded-xl p-3.5 sm:p-4 border transition-all duration-200 flex flex-col justify-between relative group">
                        
                        <!-- Checked radio badge -->
                        <div x-show="selectedShift === shift.id && !shift.is_disabled" 
                             class="absolute top-3 right-3 w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>

                        <div>
                            <!-- Shift Header & Time Range -->
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-2 h-2 rounded-full"
                                      :class="shift.is_full ? 'bg-rose-500' : 'bg-emerald-500'"></span>
                                <h3 class="text-sm sm:text-base font-extrabold text-slate-900" x-text="shift.name"></h3>
                            </div>

                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg w-fit mb-3">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="shift.time"></span>
                            </div>

                            <!-- INFORMASI SISA KUOTA SECARA REAL-TIME (Slide 2 Requirement) -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-[11px]">
                                    <span class="text-slate-500">Status Kuota:</span>
                                    <span class="font-extrabold px-1.5 py-0.5 rounded text-[10.5px]"
                                          :class="shift.is_full 
                                            ? 'bg-rose-100 text-rose-700 border border-rose-200' 
                                            : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                          x-text="shift.status_label">
                                    </span>
                                </div>

                                <!-- Real-Time Progress Bar -->
                                <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-300 rounded-full"
                                         :class="shift.is_full ? 'bg-rose-500' : (shift.percentage > 70 ? 'bg-amber-500' : 'bg-emerald-500')"
                                         :style="'width: ' + shift.percentage + '%'"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-slate-400">
                                    <span x-text="'Terisi: ' + shift.booked_count + ' pemohon'"></span>
                                    <span x-text="'Kapasitas: ' + shift.total_quota"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Indicator / Disabled state button -->
                        <div class="mt-3.5 pt-2.5 border-t border-slate-100">
                            <template x-if="shift.is_full">
                                <div class="w-full py-1.5 px-3 rounded-lg bg-rose-50 text-rose-600 font-bold text-xs text-center border border-rose-200">
                                    Shift Tidak Tersedia (Kuota Penuh)
                                </div>
                            </template>
                            <template x-if="!shift.is_full && selectedShift === shift.id">
                                <div class="w-full py-1.5 px-3 rounded-lg bg-emerald-600 text-white font-bold text-xs text-center shadow-xs">
                                    Shift Terpilih
                                </div>
                            </template>
                            <template x-if="!shift.is_full && selectedShift !== shift.id">
                                <div class="w-full py-1.5 px-3 rounded-lg bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-bold text-xs text-center transition-colors">
                                    Pilih Sesi Ini
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- 4. RINGKASAN JADWAL TERPILIH & TOMBOL MENUJU SLIDE 3 -->
        <section class="bg-gradient-to-r from-slate-900 via-slate-800 to-teal-950 text-white rounded-2xl p-4 sm:p-5 border border-slate-700 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-700/80 pb-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Ringkasan Pilihan Jadwal Anda</span>
                    <h3 class="text-base sm:text-lg font-black text-white mt-0.5">Layanan & Waktu Kunjungan Terpilih</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        Langkah 2 dari 3 Selesai
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-2.5 sm:p-3 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-slate-400 block text-[10px] mb-0.5">Jenis Layanan:</span>
                    <strong class="text-white font-bold text-xs sm:text-[13px] leading-snug block truncate" x-text="currentServiceName"></strong>
                </div>

                <div class="p-2.5 sm:p-3 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-slate-400 block text-[10px] mb-0.5">Tanggal Kunjungan:</span>
                    <strong class="text-emerald-300 font-bold text-xs sm:text-[13px] leading-snug block" x-text="formattedSelectedDate"></strong>
                </div>

                <div class="p-2.5 sm:p-3 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-slate-400 block text-[10px] mb-0.5">Sesi Shift:</span>
                    <strong class="text-white font-bold text-xs sm:text-[13px] leading-snug block" x-text="currentShiftLabel"></strong>
                </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-700/70">
                <p class="text-[11px] text-slate-300 max-w-lg">
                    Langkah berikutnya: Isi data diri pemohon dan periksa checklist syarat dokumen fisik yang wajib dibawa ke loket.
                </p>

                <button type="submit" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-400 hover:to-teal-400 text-white font-extrabold text-xs sm:text-sm shadow-lg shadow-emerald-950/40 hover:shadow-xl transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <span>Lanjut ke Formulir Data & Syarat Berkas</span>
                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
function registrationWizard(config) {
    return {
        services: config.services,
        selectedServiceId: config.selectedServiceId,
        holidaysMap: config.holidaysMap,
        selectedDate: config.initialDate,
        selectedShift: 'pagi',
        currentYear: config.currentYear,
        currentMonth: config.currentMonth,
        shifts: config.shiftsData,
        loadingShifts: false,

        monthNames: [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ],

        getServiceName(service) {
            if (service.slug === 'ktp-el' || service.id == 1) return 'PELAYAN ADMINDUK';
            if (service.slug === 'kartu-keluarga' || service.id == 2) return 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK';
            if (service.slug === 'akta-kelahiran-kematian' || service.id == 3) return 'KONSULTASI / PENGADUAN';
            if (service.slug === 'pindah-datang' || service.id == 4) return 'LOKET PRIORITAS';
            const titles = {
                'ktp-el': 'PELAYAN ADMINDUK',
                'kartu-keluarga': 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                'akta-kelahiran-kematian': 'KONSULTASI / PENGADUAN',
                'pindah-datang': 'LOKET PRIORITAS',
                'Pembuatan KTP-el': 'PELAYAN ADMINDUK',
                'Kartu Keluarga (KK)': 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                'Akta Kelahiran / Kematian': 'KONSULTASI / PENGADUAN',
                'Surat Pindah Datang (SKPWNI)': 'LOKET PRIORITAS'
            };
            return titles[service.slug] || titles[service.name] || service.name;
        },

        get currentServiceName() {
            const found = this.services.find(s => s.id == this.selectedServiceId);
            return found ? this.getServiceName(found) : '-';
        },

        get formattedSelectedDate() {
            if (!this.selectedDate) return '-';
            const parts = this.selectedDate.split('-');
            if (parts.length !== 3) return this.selectedDate;
            const d = new Date(parts[0], parts[1] - 1, parts[2]);
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            return `${dayNames[d.getDay()]}, ${d.getDate()} ${this.monthNames[d.getMonth()]} ${d.getFullYear()}`;
        },

        get currentShiftLabel() {
            const found = this.shifts.find(s => s.id === this.selectedShift);
            return found ? `${found.name} (${found.time})` : 'Shift Pagi';
        },

        get blankDays() {
            const firstDayIndex = new Date(this.currentYear, this.currentMonth, 1).getDay();
            // Monday-based index: 0 for Monday ... 6 for Sunday
            const mondayBased = (firstDayIndex + 6) % 7;
            return Array.from({ length: mondayBased }, (_, i) => i);
        },

        get monthDays() {
            const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            const today = new Date();
            today.setHours(0,0,0,0);

            const days = [];
            for (let day = 1; day <= daysInMonth; day++) {
                const dateObj = new Date(this.currentYear, this.currentMonth, day);
                const dayOfWeek = dateObj.getDay(); // 0 is Sunday, 6 is Saturday
                const yyyy = dateObj.getFullYear();
                const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
                const dd = String(day).padStart(2, '0');
                const dateString = `${yyyy}-${mm}-${dd}`;

                // Check Weekend
                const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                let isHoliday = false;
                let holidayName = '';

                if (isWeekend) {
                    isHoliday = true;
                    holidayName = dayOfWeek === 0 ? 'Akhir Pekan (Minggu)' : 'Akhir Pekan (Sabtu)';
                } else if (this.holidaysMap[dateString]) {
                    isHoliday = true;
                    holidayName = this.holidaysMap[dateString].name;
                }

                const isPast = dateObj < today;
                const isToday = dateObj.getTime() === today.getTime();

                days.push({
                    dayNumber: day,
                    dateString: dateString,
                    isHoliday: isHoliday,
                    holidayName: holidayName,
                    isPast: isPast,
                    isToday: isToday
                });
            }
            return days;
        },

        prevMonth() {
            if (this.currentMonth === 0) {
                this.currentMonth = 11;
                this.currentYear--;
            } else {
                this.currentMonth--;
            }
        },

        nextMonth() {
            if (this.currentMonth === 11) {
                this.currentMonth = 0;
                this.currentYear++;
            } else {
                this.currentMonth++;
            }
        },

        selectService(id) {
            this.selectedServiceId = id;
            this.fetchShifts();
        },

        onServiceChange() {
            this.fetchShifts();
        },

        selectDate(dateStr) {
            this.selectedDate = dateStr;
            this.fetchShifts();
        },

        selectShift(shiftId) {
            this.selectedShift = shiftId;
        },

        fetchShifts() {
            this.loadingShifts = true;
            fetch(`/api/shifts?service_id=${this.selectedServiceId}&date=${this.selectedDate}`)
                .then(res => res.json())
                .then(res => {
                    this.loadingShifts = false;
                    if (res.status === 'success' && res.data && res.data.shifts) {
                        this.shifts = res.data.shifts;

                        // If currently selected shift is full or disabled, auto-switch to first available shift
                        const current = this.shifts.find(s => s.id === this.selectedShift);
                        if (!current || current.is_disabled) {
                            const firstAvailable = this.shifts.find(s => !s.is_disabled);
                            if (firstAvailable) {
                                this.selectedShift = firstAvailable.id;
                            }
                        }
                    }
                })
                .catch(() => {
                    this.loadingShifts = false;
                });
        }
    };
}
</script>
@endpush
