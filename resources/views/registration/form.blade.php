@extends('layouts.app')

@section('title', 'Formulir Data Pemohon & Syarat Berkas - Disdukcapil Kabupaten Cirebon')

@section('content')
@php
    $customTitles = [
        'ktp-el' => 'PELAYAN ADMINDUK',
        'kartu-keluarga' => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
        'akta-kelahiran-kematian' => 'KONSULTASI / PENGADUAN',
        'pindah-datang' => 'LOKET PRIORITAS',
        'Pembuatan KTP-el' => 'PELAYAN ADMINDUK',
        'Kartu Keluarga (KK)' => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
        'Akta Kelahiran / Kematian' => 'KONSULTASI / PENGADUAN',
        'Surat Pindah Datang (SKPWNI)' => 'LOKET PRIORITAS',
    ];
    $displayServiceName = $customTitles[$selectedService->slug] ?? ($customTitles[$selectedService->name] ?? $selectedService->name);
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
     x-data="{
        serviceId: {{ $selectedService->id }},
        requirements: {{ Js::from($selectedService->requirements ?? []) }},
        checkedDocs: [],
        nik: '{{ old('nik') }}',
        fullName: '{{ old('full_name') }}',
        phone: '{{ old('phone') }}',
        district: '{{ old('district') }}',
        address: '{{ old('address') }}',
        submitting: false,
        allChecked() {
            return this.checkedDocs.length === this.requirements.length;
        },
        toggleCheck(index) {
            if (this.checkedDocs.includes(index)) {
                this.checkedDocs = this.checkedDocs.filter(i => i !== index);
            } else {
                this.checkedDocs.push(index);
            }
        },
        checkAll() {
            this.checkedDocs = this.requirements.map((_, i) => i);
        }
     }">

    <!-- Top Breadcrumb & Step Navigation -->
    <div class="mb-8">
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <a href="{{ route('home') }}" class="hover:text-emerald-700 transition-colors">Beranda</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('registration.create') }}" class="hover:text-emerald-700 transition-colors">Pilih Layanan & Jadwal</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 font-bold">Formulir Data Pemohon & Syarat Berkas</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Langkah Terakhir (Slide 3)
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                    Formulir Data Pemohon & Syarat Berkas
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Lengkapi identitas diri pemohon dan periksa kelengkapan dokumen fisik yang wajib dibawa ke loket.
                </p>
            </div>

            <!-- Steps indicator pill -->
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-2xs self-start sm:self-auto text-xs font-bold text-slate-600">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-[11px]">✓</span>
                <span class="text-emerald-800">Layanan</span>
                <span class="text-slate-300">&bull;</span>
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-[11px]">✓</span>
                <span class="text-emerald-800">Jadwal</span>
                <span class="text-slate-300">&bull;</span>
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[11px]">3</span>
                <span class="text-slate-900 font-extrabold">Data & Berkas</span>
            </div>
        </div>
    </div>

    <!-- Alert / Validation Errors -->
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1">
            <div class="font-bold flex items-center gap-2 text-rose-900">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Mohon periksa kembali formulir Anda:</span>
            </div>
            <ul class="list-disc list-inside pl-7 space-y-0.5 text-xs text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('registration.store') }}" 
          method="POST" 
          @submit="submitting = true"
          class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        @csrf

        <!-- Hidden parameters passed from Slide 2 -->
        <input type="hidden" name="service_id" value="{{ $selectedService->id }}">
        <input type="hidden" name="appointment_date" value="{{ $carbonDate->toDateString() }}">
        <input type="hidden" name="shift" value="{{ $shift }}">

        <!-- LEFT COLUMN: FORMULIR INPUT DATA DIRI (Slide 3 Requirement) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">1</span>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900">Formulir Input Data Diri</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 pl-9">
                        Pastikan data pemohon di bawah ini sesuai dengan dokumen identitas resmi yang berlaku di Kabupaten Cirebon.
                    </p>
                </div>

                <div class="space-y-4 sm:space-y-5">
                    <!-- 1. Nama Lengkap -->
                    <div>
                        <label for="full_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nama Lengkap Sesuai KTP / KK <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="{{ old('full_name') }}"
                                   required 
                                   placeholder="Contoh: Muhammad Ridwan Maulana"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-semibold text-slate-900 placeholder:font-normal placeholder:text-slate-400">
                        </div>
                    </div>

                    <!-- 2. NIK (Nomor Induk Kependudukan) -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="nik" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                NIK (Nomor Induk Kependudukan) <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] font-mono text-slate-400" 
                                  :class="nik.length === 16 ? 'text-emerald-600 font-bold' : (nik.length > 0 ? 'text-amber-600' : '')"
                                  x-text="nik.length + ' / 16 Digit'"></span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            </div>
                            <input type="text" 
                                   id="nik" 
                                   name="nik" 
                                   x-model="nik"
                                   required 
                                   maxlength="16"
                                   minlength="16"
                                   pattern="[0-9]{16}"
                                   @input="nik = nik.replace(/[^0-9]/g, '')"
                                   placeholder="3209xxxxxxxxxxxx (16 digit angka)"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-mono tracking-wider font-bold text-slate-900 placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">
                            NIK warga Kabupaten Cirebon umumnya berawalan kode wilayah <strong class="text-slate-700">3209</strong>.
                        </p>
                    </div>

                    <!-- 3. Nomor WhatsApp Aktif -->
                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-600 font-bold">
                                <span>📱</span>
                            </div>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}"
                                   required 
                                   placeholder="Contoh: 081234567890"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-semibold text-slate-900 placeholder:font-normal placeholder:text-slate-400">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Nomor ini digunakan untuk verifikasi kehadiran dan pemberitahuan panggilan nomor antrean di kantor dinas.
                        </p>
                    </div>

                    <!-- 4. Alamat Domisili di Kabupaten Cirebon -->
                    <div class="space-y-3 pt-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Alamat Domisili di Kabupaten Cirebon <span class="text-rose-500">*</span>
                        </label>

                        <!-- Pilihan Kecamatan di Kab Cirebon (40 Kecamatan Lengkap) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="district" class="block text-[11px] font-semibold text-slate-500 mb-1">
                                    Kecamatan di Kab. Cirebon <span class="text-rose-500">*</span>
                                </label>
                                <select id="district" 
                                        name="district" 
                                        required 
                                        class="w-full py-2.5 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-semibold text-slate-900 bg-white">
                                    <option value="" disabled {{ old('district') ? '' : 'selected' }}>-- Pilih Kecamatan --</option>
                                    @foreach($districts as $item)
                                        <option value="{{ $item }}" {{ old('district') === $item ? 'selected' : '' }}>
                                            Kec. {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="address" class="block text-[11px] font-semibold text-slate-500 mb-1">
                                    Desa/Kelurahan, RT/RW & Jalan <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="address" 
                                       name="address" 
                                       value="{{ old('address') }}"
                                       required 
                                       placeholder="Contoh: Desa Kemantren RT 03 / RW 02, Blok Manis"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium text-slate-900 placeholder:text-slate-400">
                            </div>
                        </div>
                    </div>

                    <!-- Email & Catatan Opsional -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Alamat Email (Opsional)
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="email@anda.com"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium">
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Catatan / Keterangan (Opsional)
                            </label>
                            <input type="text" 
                                   id="notes" 
                                   name="notes" 
                                   value="{{ old('notes') }}"
                                   placeholder="Misal: KTP rusak buram"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: PUSAT INFORMASI PERSYARATAN BERKAS (CHECKLIST) (Slide 3 Requirement) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Summary Jadwal Terpilih Card -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 text-white rounded-3xl p-6 border border-slate-700 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-700/80 pb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Jadwal Pelayanan Anda</span>
                    <a href="{{ route('registration.create', ['service' => $selectedService->slug]) }}" 
                       class="text-[11px] text-emerald-300 hover:text-white underline font-semibold flex items-center gap-1">
                        <span>Ubah Jadwal</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </a>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jenis Layanan:</span>
                        <strong class="text-white font-extrabold text-base">{{ $displayServiceName }}</strong>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-700/60">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Tanggal Kunjungan:</span>
                            <strong class="text-emerald-300 font-bold text-xs sm:text-[13px]">
                                {{ $carbonDate->locale('id')->isoFormat('dddd, D MMMM Y') }}
                            </strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Sesi Shift:</span>
                            <strong class="text-white font-bold text-xs sm:text-sm">
                                {{ $currentShift['name'] }}
                            </strong>
                            <span class="text-[10px] text-emerald-400 block font-medium">{{ $currentShift['time'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PUSAT INFORMASI PERSYARATAN BERKAS (CHECKLIST INTERAKTIF) (Slide 3 Requirement) -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">2</span>
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Pusat Informasi Syarat Berkas</h3>
                        </div>
                        <button type="button" 
                                @click="checkAll()" 
                                class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 hover:underline">
                            Centang Semua
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 pl-9">
                        Berdasarkan layanan <strong class="text-slate-700">{{ $displayServiceName }}</strong>, berikut daftar dokumen fisik yang <span class="text-rose-600 font-bold">wajib dibawa</span>. Silakan periksa dan centang sebelum datang ke loket:
                    </p>
                </div>

                <!-- Checklist Completion Meter -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80 space-y-1.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-700">Kesiapan Dokumen Fisik:</span>
                        <span class="font-extrabold" 
                              :class="allChecked() ? 'text-emerald-700' : 'text-slate-600'"
                              x-text="checkedDocs.length + ' dari ' + requirements.length + ' Dokumen Siap'"></span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 transition-all duration-300 rounded-full"
                             :style="'width: ' + ((checkedDocs.length / Math.max(1, requirements.length)) * 100) + '%'"></div>
                    </div>
                </div>

                <!-- Interactive Checklist Items (Slide 3 Requirement) -->
                <div class="space-y-2.5">
                    <template x-for="(req, index) in requirements" :key="index">
                        <div @click="toggleCheck(index)"
                             :class="checkedDocs.includes(index) ? 'border-emerald-500 bg-emerald-50/50 shadow-2xs' : 'border-slate-200 bg-white hover:border-slate-300'"
                             class="p-3.5 rounded-2xl border transition-all cursor-pointer flex items-start gap-3 select-none group">
                            
                            <!-- Custom Checkbox -->
                            <div class="w-5 h-5 rounded-lg border flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                                 :class="checkedDocs.includes(index) ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300 group-hover:border-emerald-400 bg-white'">
                                <svg x-show="checkedDocs.includes(index)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>

                            <div class="text-xs leading-relaxed"
                                 :class="checkedDocs.includes(index) ? 'text-emerald-950 font-semibold' : 'text-slate-700'">
                                <span x-text="req"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Friendly Reminder Alert -->
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="leading-normal">
                        Membawa berkas lengkap asli dan fotokopi sesuai checklist mempercepat waktu pemrosesan di loket Disdukcapil Sumber.
                    </p>
                </div>

                <!-- TOMBOL KONFIRMASI (Slide 3 Requirement) -->
                <div class="pt-2">
                    <button type="submit" 
                            :disabled="submitting"
                            class="w-full group relative inline-flex items-center justify-center gap-3 py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-500 disabled:opacity-50 text-white font-extrabold text-base shadow-xl shadow-emerald-700/25 hover:shadow-2xl hover:shadow-emerald-600/35 transition-all duration-300 transform hover:-translate-y-0.5 cursor-pointer">
                        
                        <span x-show="!submitting" class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span class="tracking-wide">Kirim Pendaftaran & Dapatkan Tiket</span>
                        </span>

                        <span x-show="submitting" x-cloak class="flex items-center gap-2">
                            <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses Tiket Antrean...</span>
                        </span>
                    </button>
                    
                    <p class="text-center text-[11px] text-slate-400 mt-2">
                        Nomor antrean dan kode QR resmi akan langsung diterbitkan setelah Anda mengirim formulir ini.
                    </p>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
