@extends('layouts.app')

@section('title', 'Bukti Tiket Antrean - ' . $appointment->formatted_queue_number . ' - Disdukcapil Kab. Cirebon')

@push('styles')
<style>
    @media print {
        @page {
            size: portrait;
            margin: 6mm 8mm;
        }

        html, body {
            background: #ffffff !important;
            color: #0f172a !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 12px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        header, footer, nav, .print\:hidden {
            display: none !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
        }

        .max-w-4xl {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 auto !important;
        }

        #ticketCard {
            border: 2px solid #0f172a !important;
            border-radius: 16px !important;
            box-shadow: none !important;
            max-width: 520px !important;
            margin: 0 auto !important;
            padding: 0 !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            background: #ffffff !important;
            overflow: hidden !important;
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 print:p-0 print:m-0 print:max-w-none"
     x-data="{
        copied: false,
        isDownloadingPdf: false,
        isSavingImage: false,
        modalSimulasiQr: false,
        init() {
            try {
                const currentTicket = {
                    ticket_number: '{{ $appointment->ticket_number }}',
                    queue_number: '{{ $appointment->formatted_queue_number }}',
                    full_name: '{{ $appointment->full_name }}',
                    masked_nik: '{{ $appointment->masked_nik }}',
                    nik: '{{ $appointment->masked_nik }}',
                    phone: '{{ $appointment->phone }}',
                    service_name: '{{ $appointment->service->name }}',
                    appointment_date: '{{ $appointment->appointment_date->locale("id")->isoFormat("dddd, D MMMM Y") }}',
                    shift_name: 'Shift {{ ucfirst($appointment->shift) }} ({{ $shiftTimes[$appointment->shift] ?? "" }})',
                    arrival_time: '{{ $shiftArrivalTimes[$appointment->shift] ?? "" }}',
                    status: '{{ $appointment->status }}',
                    status_badge: '{{ match ($appointment->status) { "confirmed" => "Terdaftar / Menunggu", "checked_in" => "Sudah Hadir di Loket", "serving" => "Sedang Diproses", "completed" => "Selesai", "cancelled" => "Dibatalkan", default => $appointment->status } }}',
                    ticket_url: '{{ route("ticket.show", ["ticketNumber" => $appointment->ticket_number]) }}',
                    saved_at: new Date().toISOString()
                };
                localStorage.setItem('capil_last_ticket', JSON.stringify(currentTicket));
                let tickets = JSON.parse(localStorage.getItem('capil_active_tickets') || '[]');
                tickets = tickets.filter(t => t.ticket_number !== currentTicket.ticket_number);
                tickets.unshift(currentTicket);
                localStorage.setItem('capil_active_tickets', JSON.stringify(tickets.slice(0, 10)));
            } catch (e) {
                console.warn('LocalStorage error:', e);
            }
        },
        copyTicket() {
            navigator.clipboard.writeText('{{ $appointment->formatted_queue_number }}');
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },
        downloadPdf() {
            this.isDownloadingPdf = true;
            const element = document.getElementById('ticketCard');
            const opt = {
                margin:       [8, 8, 8, 8],
                filename:     'Tiket-Antrean-{{ $appointment->formatted_queue_number }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            if (typeof html2pdf !== 'undefined') {
                html2pdf().set(opt).from(element).save().then(() => {
                    this.isDownloadingPdf = false;
                }).catch(err => {
                    console.error('Error generating PDF:', err);
                    this.isDownloadingPdf = false;
                    window.print();
                });
            } else {
                this.isDownloadingPdf = false;
                window.print();
            }
        },
        saveScreenshot() {
            this.isSavingImage = true;
            const element = document.getElementById('ticketCard');
            if (typeof html2canvas !== 'undefined') {
                html2canvas(element, { scale: 2, useCORS: true }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'Tiket-Antrean-{{ $appointment->formatted_queue_number }}.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    this.isSavingImage = false;
                }).catch(err => {
                    console.error('Error saving image:', err);
                    this.isSavingImage = false;
                    alert('Gunakan tangkapan layar (screenshot) bawaan perangkat Anda untuk menyimpan tiket.');
                });
            } else {
                this.isSavingImage = false;
                alert('Silakan ambil tangkapan layar (screenshot) langsung di ponsel Anda.');
            }
        }
     }">

    <!-- Top Breadcrumb & Step Navigation (Slide 4 Indicator) -->
    <div class="mb-6 print:hidden">
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
            <a href="{{ route('home') }}" class="hover:text-emerald-700 transition-colors">Beranda</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('registration.create') }}" class="hover:text-emerald-700 transition-colors">Pilih Layanan & Jadwal</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-800 font-bold">Bukti Tiket Digital (Slide 4)</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-300 inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    Slide 4: Halaman Tiket Digital / Bukti Pendaftaran
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1">
                    Tiket Elektronik Antrean Resmi (E-Ticket)
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Pendaftaran Anda berhasil! Tunjukkan tiket ini atau QR Code kepada petugas loket saat berkunjung.
                </p>
            </div>

            <!-- Steps Completed Indicator -->
            <div class="flex items-center gap-1.5 bg-white px-3.5 py-2 rounded-2xl border border-slate-200 shadow-2xs self-start sm:self-auto text-xs font-bold text-slate-600">
                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                <span class="text-emerald-800 hidden sm:inline">Layanan</span>
                <span class="text-slate-300">&bull;</span>
                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                <span class="text-emerald-800 hidden sm:inline">Jadwal</span>
                <span class="text-slate-300">&bull;</span>
                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                <span class="text-emerald-800 hidden sm:inline">Berkas</span>
                <span class="text-slate-300">&bull;</span>
                <span class="w-5 h-5 rounded-full bg-emerald-700 text-white flex items-center justify-center text-[10px] ring-2 ring-emerald-300">4</span>
                <span class="text-emerald-950 font-black">Tiket Terbit</span>
            </div>
        </div>
    </div>

    <!-- Success Celebration Alert Banner -->
    <div class="mb-6 p-4 sm:p-5 rounded-3xl bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white shadow-xl shadow-emerald-700/20 flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 shadow-inner">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-extrabold text-base sm:text-lg">Pendaftaran Antrean Online Berhasil!</h2>
                    <span class="bg-amber-400 text-slate-900 text-[10px] font-black uppercase px-2 py-0.5 rounded-full tracking-wider">RESMI</span>
                </div>
                <p class="text-xs sm:text-sm text-emerald-100 mt-0.5">
                    Nomor antrean unik Anda telah diamankan. Simpan tangkapan layar (screenshot), unduh PDF, atau cetak bukti tiket ini.
                </p>
            </div>
        </div>

        <!-- Quick Primary Action Buttons at Top -->
        <div class="flex items-center gap-2 shrink-0 self-end md:self-auto">
            <button type="button" 
                    @click="downloadPdf()" 
                    :disabled="isDownloadingPdf"
                    class="px-4 py-2.5 bg-white text-emerald-900 hover:bg-emerald-50 rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-1.5 cursor-pointer">
                <svg x-show="!isDownloadingPdf" class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <svg x-show="isDownloadingPdf" class="animate-spin w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span x-text="isDownloadingPdf ? 'Menyiapkan PDF...' : 'Download Tiket (PDF)'"></span>
            </button>

            <button type="button" 
                    onclick="window.print()" 
                    class="px-4 py-2.5 bg-emerald-500/30 hover:bg-emerald-500/40 text-white border border-emerald-400/40 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MAIN DIGITAL E-TICKET CARD (CLEAN & SCREENSHOT / PRINT OPTIMIZED) -->
    <!-- ================================================================= -->
    <div id="ticketCard" class="bg-white rounded-3xl border border-slate-200/90 shadow-2xl overflow-hidden relative print:border-2 print:border-slate-800 print:shadow-none print:m-0 print:p-0 print:rounded-2xl">
        
        <!-- Official Government Top Header -->
        <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 text-white p-6 sm:p-8 relative overflow-hidden border-b-4 border-amber-400 print:p-4 print:border-b-2">
            <!-- Background Security Guilloche Pattern Decor -->
            <div class="absolute inset-0 opacity-10 pointer-events-none bg-grid-pattern"></div>
            
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-18 sm:w-18 sm:h-20 bg-white/10 rounded-2xl p-2 backdrop-blur-xs flex items-center justify-center border border-white/20 shrink-0 print:w-12 print:h-12 print:p-1">
                        <img src="{{ asset('images/kabcirebon.png') }}" alt="Lambang Pemkab Cirebon" class="w-full h-full object-contain drop-shadow-md">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] sm:text-[11px] font-black tracking-widest text-amber-300 uppercase">PEMERINTAH KABUPATEN CIREBON</span>
                            <span class="hidden sm:inline-block w-1 h-1 rounded-full bg-emerald-400"></span>
                            <span class="text-[10px] text-emerald-200 hidden sm:inline-block font-mono">SIAK TERPADU</span>
                        </div>
                        <h2 class="text-lg sm:text-2xl font-black text-white tracking-tight leading-tight mt-0.5 print:text-base">
                            Dinas Kependudukan & Pencatatan Sipil
                        </h2>
                        <p class="text-xs text-slate-300 mt-0.5 print:text-[10px]">
                            Bukti Pendaftaran Antrean Mandiri Online Resmi (E-Ticket)
                        </p>
                    </div>
                </div>

                <!-- Right Security Badge -->
                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 shrink-0">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-500/30 text-emerald-200 border border-emerald-400/50 uppercase tracking-wider flex items-center gap-1.5 print:text-[10px] print:py-0.5 print:px-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping print:hidden"></span>
                        TERVERIFIKASI AKTIF
                    </span>
                    <span class="text-[11px] font-mono text-slate-300 tracking-widest print:text-[9px]">
                        KODE: {{ $appointment->ticket_number }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Perforated Ticket Divider (Notch Cutout Boarding-Pass Style) -->
        <div class="relative h-6 bg-slate-100 flex items-center justify-between overflow-hidden border-y border-dashed border-slate-300 print:hidden">
            <div class="w-6 h-6 -ml-3 rounded-full bg-slate-50 border-r border-slate-300"></div>
            <div class="text-[10px] font-mono tracking-widest text-slate-400 uppercase font-semibold">
                &bull; &bull; &bull; SOBEK / POTONGAN BUKTI ELEKTRONIK &bull; &bull; &bull;
            </div>
            <div class="w-6 h-6 -mr-3 rounded-full bg-slate-50 border-l border-slate-300"></div>
        </div>

        <!-- Ticket Body Content -->
        <div class="p-6 sm:p-8 space-y-7 print:p-4 print:space-y-3">
            
            <!-- HERO: Nomor Antrean Unik & QR Code Box -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center bg-gradient-to-br from-emerald-50/60 via-slate-50 to-teal-50/40 p-6 sm:p-7 rounded-3xl border-2 border-dashed border-emerald-300 relative print:grid-cols-12 print:gap-4 print:p-3 print:bg-white print:border-slate-300 print:rounded-xl">
                
                <!-- Left: Nomor Antrean Unik (Slide 4 Requirement) -->
                <div class="md:col-span-7 print:col-span-7 text-center md:text-left print:text-left space-y-3 print:space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs font-bold uppercase tracking-wider print:text-[10px] print:py-0.5 print:px-2">
                        <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>NOMOR ANTREAN UNIK ANDA</span>
                    </div>

                    <!-- Unique Queue Number Display (Contoh: CAPIL-CBR-A012) -->
                    <div class="flex items-center justify-center md:justify-start gap-3">
                        <div class="text-4xl sm:text-5xl lg:text-6xl font-black text-emerald-800 tracking-tight font-mono select-all drop-shadow-xs print:text-3xl print:text-slate-900"
                             id="queueNumberText">
                            {{ $appointment->formatted_queue_number }}
                        </div>

                        <!-- Copy Button -->
                        <button type="button" 
                                @click="copyTicket()" 
                                title="Salin Nomor Antrean"
                                class="p-2 sm:p-2.5 rounded-xl bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition-colors shadow-2xs print:hidden cursor-pointer">
                            <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <svg x-show="copied" class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>

                    <div x-show="copied" x-transition class="text-xs font-bold text-emerald-700 print:hidden">
                        ✓ Nomor antrean berhasil disalin ke papan klip!
                    </div>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 text-xs font-semibold text-slate-600 print:text-[11px]">
                        <span class="bg-white px-3 py-1 rounded-lg border border-slate-200 print:px-2 print:py-0.5">
                            Loket Pelayanan: <strong>Lantai 1 Disdukcapil</strong>
                        </span>
                        <span class="bg-white px-3 py-1 rounded-lg border border-slate-200 font-mono print:px-2 print:py-0.5">
                            ID: {{ $appointment->ticket_number }}
                        </span>
                    </div>
                </div>

                <!-- Right: QR Code Validasi Petugas (Slide 4 Requirement) -->
                <div class="md:col-span-5 print:col-span-5 flex flex-col items-center justify-center text-center p-4 print:p-2 bg-white rounded-2xl border border-slate-200 shadow-xs relative print:border-slate-300">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-1.5 print:text-[10px] print:mb-1">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M4 8h4m-4 8h4m8-8h4M4 4h4v4H4V4zm12 0h4v4h-4V4zM4 16h4v4H4v-4z"/></svg>
                        <span>KODE VALIDASI PETUGAS</span>
                    </div>

                    <!-- QR Code Image (Aktif / Scannable) with Fallback -->
                    @php
                        $qrPayload = route('ticket.show', ['ticketNumber' => $appointment->ticket_number]);
                        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=8&data=' . urlencode($qrPayload);
                    @endphp

                    <div class="relative p-2.5 bg-white border-2 border-emerald-400 rounded-2xl shadow-sm group print:p-1.5 print:rounded-xl">
                        <!-- Corner Markers for High-Tech Scanner Look -->
                        <div class="absolute -top-1 -left-1 w-3.5 h-3.5 border-t-2 border-l-2 border-emerald-700"></div>
                        <div class="absolute -top-1 -right-1 w-3.5 h-3.5 border-t-2 border-r-2 border-emerald-700"></div>
                        <div class="absolute -bottom-1 -left-1 w-3.5 h-3.5 border-b-2 border-l-2 border-emerald-700"></div>
                        <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 border-b-2 border-r-2 border-emerald-700"></div>

                        <img src="{{ $qrApiUrl }}" 
                             alt="QR Code Validasi Antrean {{ $appointment->formatted_queue_number }}" 
                             class="w-36 h-36 sm:w-40 sm:h-40 object-contain mx-auto print:w-28 print:h-28"
                             loading="eager">
                    </div>

                    <!-- Visual Barcode Representation (Code 128) -->
                    <div class="mt-2 pt-1.5 border-t border-slate-200 w-full flex flex-col items-center">
                        <div class="flex items-center justify-center h-7 w-36 overflow-hidden bg-white text-slate-900">
                            <svg class="h-7 w-full" viewBox="0 0 160 28" fill="currentColor">
                                <rect x="0" y="0" width="2" height="28"/>
                                <rect x="4" y="0" width="4" height="28"/>
                                <rect x="10" y="0" width="1" height="28"/>
                                <rect x="13" y="0" width="3" height="28"/>
                                <rect x="18" y="0" width="2" height="28"/>
                                <rect x="22" y="0" width="5" height="28"/>
                                <rect x="29" y="0" width="2" height="28"/>
                                <rect x="33" y="0" width="4" height="28"/>
                                <rect x="39" y="0" width="1" height="28"/>
                                <rect x="42" y="0" width="3" height="28"/>
                                <rect x="47" y="0" width="5" height="28"/>
                                <rect x="54" y="0" width="2" height="28"/>
                                <rect x="58" y="0" width="4" height="28"/>
                                <rect x="64" y="0" width="1" height="28"/>
                                <rect x="67" y="0" width="3" height="28"/>
                                <rect x="72" y="0" width="2" height="28"/>
                                <rect x="76" y="0" width="5" height="28"/>
                                <rect x="83" y="0" width="3" height="28"/>
                                <rect x="88" y="0" width="1" height="28"/>
                                <rect x="91" y="0" width="4" height="28"/>
                                <rect x="97" y="0" width="2" height="28"/>
                                <rect x="101" y="0" width="5" height="28"/>
                                <rect x="108" y="0" width="2" height="28"/>
                                <rect x="112" y="0" width="3" height="28"/>
                                <rect x="117" y="0" width="1" height="28"/>
                                <rect x="120" y="0" width="4" height="28"/>
                                <rect x="126" y="0" width="2" height="28"/>
                                <rect x="130" y="0" width="5" height="28"/>
                                <rect x="137" y="0" width="3" height="28"/>
                                <rect x="142" y="0" width="2" height="28"/>
                                <rect x="146" y="0" width="4" height="28"/>
                                <rect x="152" y="0" width="1" height="28"/>
                                <rect x="155" y="0" width="3" height="28"/>
                            </svg>
                        </div>
                        <span class="text-[9px] font-mono tracking-widest text-slate-600 font-semibold">{{ $appointment->ticket_number }}</span>
                    </div>

                    <p class="text-[11px] text-slate-500 mt-1.5 max-w-[220px] leading-tight print:text-[9px]">
                        Pindai untuk validasi otomatis petugas loket saat tiba di kantor Disdukcapil.
                    </p>

                    <!-- Interactive Officer Scan Demo Button -->
                    <button type="button" 
                            @click="modalSimulasiQr = true"
                            class="mt-2 text-[11px] font-bold text-emerald-700 hover:text-emerald-800 hover:underline flex items-center gap-1 print:hidden cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Simulasi Scan Petugas</span>
                    </button>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- PRINT-ONLY COMPACT SLIP DETAILS (FITS EXACTLY ON 1 SHEET OF PAPER)-->
            <!-- ================================================================= -->
            <div class="hidden print:block border-t-2 border-dashed border-slate-300 pt-3 text-xs space-y-2 text-slate-800">
                <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg">
                        <span class="text-slate-500 block text-[9px] uppercase font-bold">Layanan</span>
                        <strong class="text-slate-900 block font-bold text-xs">{{ $appointment->service->name }}</strong>
                    </div>
                    <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg">
                        <span class="text-slate-500 block text-[9px] uppercase font-bold">Jadwal & Shift</span>
                        <strong class="text-slate-900 block font-bold text-xs">{{ $appointment->appointment_date->locale('id')->isoFormat('D MMM Y') }} (Shift {{ ucfirst($appointment->shift) }})</strong>
                        <span class="text-slate-600 block text-[10px]">{{ $shiftTimes[$appointment->shift] ?? '' }}</span>
                    </div>
                    <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg">
                        <span class="text-slate-500 block text-[9px] uppercase font-bold">Nama Pemohon</span>
                        <strong class="text-slate-900 block font-bold text-xs">{{ $appointment->full_name }}</strong>
                    </div>
                    <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg">
                        <span class="text-slate-500 block text-[9px] uppercase font-bold">NIK Pemohon</span>
                        <strong class="text-slate-900 block font-mono text-xs">{{ $appointment->masked_nik }}</strong>
                    </div>
                </div>

                <div class="p-2 bg-amber-50 border border-amber-300 rounded-lg text-[10px] text-amber-900 leading-tight">
                    <strong>Catatan:</strong> Harap hadir 15 menit sebelum shift dimulai dengan membawa dokumen fisik lengkap (asli & fotokopi). Tunjukkan barcode ini kepada petugas loket Disdukcapil Kab. Cirebon.
                </div>

                <div class="flex items-center justify-between text-[9px] text-slate-500 pt-1 border-t border-slate-200 font-mono">
                    <span>Lokasi: Jl. Sunan Muria Komplek Pemda No. 04, Sumber, Kab. Cirebon</span>
                    <span>Dicetak: {{ now()->locale('id')->isoFormat('D/MM/Y HH:mm') }} WIB</span>
                </div>
            </div>

            <!-- DETAIL TIKET LENGKAP (Clean 6-Card Grid) -->
            <div class="print:hidden">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                    <span>INFORMASI LENGKAP PENDAFTARAN</span>
                    <span class="h-px bg-slate-200 flex-1"></span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs sm:text-sm">
                    
                    <!-- 1. Nama Pemohon -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 text-xs block mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Nama Pemohon:
                        </span>
                        <strong class="text-slate-900 font-extrabold text-sm sm:text-base uppercase block">
                            {{ $appointment->full_name }}
                        </strong>
                        <span class="text-[11px] text-slate-500 block mt-0.5">Pemilik Hak Layanan</span>
                    </div>

                    <!-- 2. NIK Pemohon (Disensor Sebagian Sesuai Spesifikasi) -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 text-xs mb-1 flex items-center justify-between">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                NIK Pemohon:
                            </span>
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-medium">
                                Sensor PDP
                            </span>
                        </span>
                        <strong class="text-slate-900 font-extrabold font-mono text-sm sm:text-base tracking-wider block">
                            {{ $appointment->masked_nik }}
                        </strong>
                        <span class="text-[11px] text-slate-500 block mt-0.5">Disensor untuk privasi kependudukan</span>
                    </div>

                    <!-- 3. Layanan yang Dipilih -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 text-xs block mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Layanan yang Dipilih:
                        </span>
                        <strong class="text-emerald-900 font-black text-sm sm:text-base block">
                            {{ $appointment->service->name }}
                        </strong>
                        <span class="text-[11px] text-slate-500 block mt-0.5">Kategori: Administrasi Kependudukan</span>
                    </div>

                    <!-- 4. Tanggal Kunjungan -->
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                        <span class="text-emerald-800 text-xs block mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Tanggal Kunjungan:
                        </span>
                        <strong class="text-emerald-950 font-black text-sm sm:text-base block">
                            {{ $appointment->appointment_date->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </strong>
                        <span class="text-[11px] text-emerald-800 font-semibold block mt-0.5">
                            Hari Kerja Resmi Disdukcapil
                        </span>
                    </div>

                    <!-- 5. Shift & Jam Pelayanan -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 text-xs block mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Sesi Waktu / Shift:
                        </span>
                        <strong class="text-slate-900 font-black text-sm sm:text-base block">
                            Shift {{ ucfirst($appointment->shift) }}
                        </strong>
                        <span class="text-[11px] font-bold text-emerald-700 block mt-0.5">
                            {{ $shiftTimes[$appointment->shift] ?? '' }}
                        </span>
                    </div>

                    <!-- 6. Wajib Hadir (15 Menit Sebelum Shift) -->
                    <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-300">
                        <span class="text-amber-900 text-xs block mb-1 flex items-center gap-1 font-bold">
                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Waktu Maksimal Hadir:
                        </span>
                        <strong class="text-amber-950 font-black text-sm sm:text-base block">
                            Pukul {{ $shiftArrivalTimes[$appointment->shift] ?? '15 Menit Sebelum Shift' }}
                        </strong>
                        <span class="text-[11px] text-amber-800 font-semibold block mt-0.5">
                            (15 menit sebelum loket dibuka)
                        </span>
                    </div>

                    <!-- 7. Nomor WhatsApp -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 text-xs block mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            No. WhatsApp Terdaftar:
                        </span>
                        <strong class="text-slate-900 font-extrabold text-sm block">
                            {{ $appointment->phone }}
                        </strong>
                        <span class="text-[11px] text-slate-500 block mt-0.5">Untuk notifikasi & konfirmasi</span>
                    </div>

                    <!-- 8. Alamat Domisili -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 sm:col-span-2">
                        <span class="text-slate-500 text-xs block mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Alamat Domisili Pemohon:
                        </span>
                        <strong class="text-slate-900 font-bold text-sm block">
                            {{ $appointment->address }}
                        </strong>
                        <span class="text-[11px] text-slate-500 block mt-0.5">Kabupaten Cirebon, Jawa Barat</span>
                    </div>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- PERINGATAN / REMINDER (SESUAI PERSYARATAN RESMI SPESIFIKASI)      -->
            <!-- ================================================================= -->
            <div class="p-5 sm:p-6 rounded-3xl bg-amber-50/90 border-2 border-amber-300 text-amber-950 space-y-3 shadow-xs print:hidden">
                <div class="flex items-center gap-2.5 text-amber-900 font-black text-sm sm:text-base">
                    <div class="w-8 h-8 rounded-xl bg-amber-400/40 text-amber-900 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <span>PERINGATAN & REMINDER KEHADIRAN:</span>
                </div>

                <!-- Exact user requested quote: -->
                <div class="p-3.5 bg-white/90 rounded-2xl border border-amber-300 font-extrabold text-sm sm:text-base text-amber-950 leading-relaxed shadow-2xs">
                    &ldquo;Harap hadir 15 menit sebelum shift dimulai dengan membawa dokumen fisik lengkap sesuai persyaratan.&rdquo;
                </div>

                <!-- Practical checklist items for citizen convenience -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-amber-900 pt-1">
                    <div class="flex items-start gap-2 bg-amber-100/60 p-2.5 rounded-xl border border-amber-200">
                        <span class="text-amber-700 font-black">1.</span>
                        <span>Tunjukkan <strong>Nomor Antrean ({{ $appointment->formatted_queue_number }})</strong> atau QR Code ini kepada petugas loket kedatangan.</span>
                    </div>

                    <div class="flex items-start gap-2 bg-amber-100/60 p-2.5 rounded-xl border border-amber-200">
                        <span class="text-amber-700 font-black">2.</span>
                        <span>Bawa seluruh dokumen fisik <strong>asli dan fotokopi</strong> sesuai persyaratan layanan {{ $appointment->service->name }}.</span>
                    </div>

                    <div class="flex items-start gap-2 bg-amber-100/60 p-2.5 rounded-xl border border-amber-200">
                        <span class="text-amber-700 font-black">3.</span>
                        <span>Pemberian nomor antrean fisik di loket akan disesuaikan dengan kehadiran pemohon di lokasi.</span>
                    </div>

                    <div class="flex items-start gap-2 bg-amber-100/60 p-2.5 rounded-xl border border-amber-200">
                        <span class="text-amber-700 font-black">4.</span>
                        <span>Jika terlambat melewati jam shift ({{ $shiftTimes[$appointment->shift] ?? '' }}), pendaftaran dianggap gugur.</span>
                    </div>
                </div>

                <!-- Document requirements reminder from selected service -->
                @if (!empty($appointment->service->requirements) && count($appointment->service->requirements) > 0)
                    <div class="mt-3 pt-3 border-t border-amber-200">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 block mb-1.5">
                            Dokumen Fisik Wajib Dibawa untuk {{ $appointment->service->name }}:
                        </span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($appointment->service->requirements as $req)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-amber-200 text-xs font-semibold text-amber-950">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $req }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Lokasi & Petunjuk Kedatangan Disdukcapil -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs print:hidden">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <strong class="text-slate-900 font-extrabold text-sm block">Lokasi Pelayanan Disdukcapil Kabupaten Cirebon</strong>
                        <p class="text-slate-600 mt-0.5 leading-relaxed">
                            Jl. Sunan Muria Komplek Pemda No. 04, Sumber, Kec. Sumber, Kabupaten Cirebon, Jawa Barat 45611.
                        </p>
                    </div>
                </div>

                <a href="https://maps.google.com/?q=Disdukcapil+Kabupaten+Cirebon" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white border border-slate-300 hover:border-emerald-500 hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 rounded-xl font-bold transition-all shrink-0 print:hidden shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Buka Peta Google Maps</span>
                </a>
            </div>
        </div>

        <!-- Official Security Ticket Footer -->
        <div class="bg-slate-50 p-6 sm:p-8 border-t border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-500 print:hidden">
            <div class="text-center md:text-left space-y-1">
                <div class="flex items-center justify-center md:justify-start gap-2 font-mono text-[11px] text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>TTE Tervalidasi Otomatis &bull; SIAK Disdukcapil Kab. Cirebon</span>
                </div>
                <div class="text-[11px] text-slate-400">
                    Waktu Terbit: {{ $appointment->created_at ? $appointment->created_at->locale('id')->isoFormat('D MMMM Y HH:mm:ss') : now()->locale('id')->isoFormat('D MMMM Y HH:mm:ss') }} WIB &bull; Hash: <span class="font-mono text-slate-600">{{ strtoupper(substr(hash('sha256', $appointment->ticket_number . $appointment->nik), 0, 16)) }}</span>
                </div>
            </div>

            <!-- Digital Seal Simulation Stamp -->
            <div class="flex items-center gap-3">
                <div class="border-2 border-emerald-600/40 rounded-xl px-3 py-1.5 text-center text-[10px] font-black tracking-widest text-emerald-800 uppercase transform -rotate-2">
                    <div class="text-[8px] text-emerald-600">DISDUKCAPIL KAB. CIREBON</div>
                    <span>DOKUMEN RESMI</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- BOTTOM ACTION TOOLBAR (SESUAI SPESIFIKASI: DOWNLOAD / CETAK / BAGI)-->
    <!-- ================================================================= -->
    <div class="mt-8 bg-white p-6 rounded-3xl border border-slate-200 shadow-lg flex flex-col lg:flex-row items-center justify-between gap-4 print:hidden">
        
        <!-- Left: Quick info -->
        <div class="text-center lg:text-left">
            <h4 class="font-extrabold text-sm text-slate-900">Butuh bantuan atau ingin menyimpan bukti pendaftaran?</h4>
            <p class="text-xs text-slate-500 mt-0.5">Pilih salah satu metode penyimpanan di bawah agar tiket tidak hilang.</p>
        </div>

        <!-- Right: Action Buttons (Slide 4 Requirement) -->
        <div class="flex flex-wrap items-center justify-center gap-2.5 w-full lg:w-auto">
            
            <!-- 1. Tombol Download Tiket (PDF) -->
            <button type="button" 
                    @click="downloadPdf()" 
                    :disabled="isDownloadingPdf"
                    class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-md shadow-emerald-600/25 transition-all flex items-center gap-2 cursor-pointer">
                <svg x-show="!isDownloadingPdf" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <svg x-show="isDownloadingPdf" class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span x-text="isDownloadingPdf ? 'Menyiapkan PDF...' : 'Download Tiket (PDF)'"></span>
            </button>

            <!-- 2. Tombol Cetak Tiket -->
            <button type="button" 
                    onclick="window.print()" 
                    class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white font-extrabold rounded-2xl shadow-md transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Tiket</span>
            </button>

            <!-- 3. Tombol Simpan Screenshot / Gambar -->
            <button type="button" 
                    @click="saveScreenshot()" 
                    :disabled="isSavingImage"
                    class="px-4 py-3 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold rounded-2xl transition-all flex items-center gap-2 cursor-pointer shadow-2xs">
                <svg x-show="!isSavingImage" class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <svg x-show="isSavingImage" class="animate-spin w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span x-text="isSavingImage ? 'Menyimpan...' : 'Simpan Gambar'"></span>
            </button>

            <!-- 4. Tombol Bagikan ke WhatsApp -->
            @php
                $waText = "Halo, ini bukti pendaftaran antrean online Disdukcapil Kab. Cirebon saya:\n\n"
                    . "• No. Antrean: " . $appointment->formatted_queue_number . "\n"
                    . "• Layanan: " . $appointment->service->name . "\n"
                    . "• Tanggal: " . $appointment->appointment_date->locale('id')->isoFormat('dddd, D MMMM Y') . "\n"
                    . "• Shift: " . ucfirst($appointment->shift) . " (" . ($shiftTimes[$appointment->shift] ?? '') . ")\n"
                    . "• Hadir Pukul: " . ($shiftArrivalTimes[$appointment->shift] ?? '') . "\n\n"
                    . "Lihat Tiket Online: " . route('ticket.show', ['ticketNumber' => $appointment->ticket_number]);
                $waUrl = 'https://api.whatsapp.com/send?text=' . urlencode($waText);
            @endphp
            <a href="{{ $waUrl }}" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="px-4 py-3 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold rounded-2xl transition-all flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                <span>Kirim WA</span>
            </a>

            <!-- 5. Navigasi Beranda -->
            <a href="{{ route('home') }}" 
               class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition-all shadow-2xs">
                Beranda
            </a>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL: SIMULASI SCAN PETUGAS (INTERACTIVE DEMO)                   -->
    <!-- ================================================================= -->
    <div x-show="modalSimulasiQr" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto print:hidden" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalSimulasiQr" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                 @click="modalSimulasiQr = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalSimulasiQr" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                
                <div class="bg-gradient-to-r from-emerald-800 to-teal-900 p-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm">Simulasi Validasi Petugas Loket</h3>
                            <p class="text-[11px] text-emerald-200">Scanner SIAK Disdukcapil Cirebon</p>
                        </div>
                    </div>
                    <button type="button" @click="modalSimulasiQr = false" class="text-white/70 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="text-center py-2">
                        <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                            VALIDASI BERHASIL
                        </span>
                        <h4 class="text-lg font-black text-slate-900 mt-2">
                            {{ $appointment->formatted_queue_number }}
                        </h4>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Data pemohon terdaftar sah di sistem antrean Disdukcapil.
                        </p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pemohon:</span>
                            <span class="font-bold text-slate-900">{{ $appointment->full_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">NIK (Privasi):</span>
                            <span class="font-mono font-bold text-slate-900">{{ $appointment->masked_nik }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Layanan:</span>
                            <span class="font-bold text-emerald-800">{{ $appointment->service->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jadwal:</span>
                            <span class="font-bold text-slate-900">{{ $appointment->appointment_date->format('d/m/Y') }} (Shift {{ ucfirst($appointment->shift) }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Status Saat Ini:</span>
                            <span class="font-bold text-emerald-700">Siap Dilayani di Loket</span>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 text-center">
                        Ketika tiba di lokasi, petugas loket akan memindai QR code ini pada barcode scanner untuk mencetak nomor loket fisik Anda.
                    </p>

                    <button type="button" 
                            @click="modalSimulasiQr = false" 
                            class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors">
                        Tutup Simulasi
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Client-side PDF & Canvas Libraries (HTML2PDF & HTML2Canvas for 1-click Download) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<!-- Dedicated High-Quality Print Stylesheet -->
<style>
@media print {
    /* Hide everything except the ticket card */
    body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    header, footer, nav, .print\:hidden, [x-cloak] {
        display: none !important;
    }
    #ticketCard {
        border: 2px solid #000 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        page-break-inside: avoid !important;
    }
    #queueNumberText {
        font-size: 48pt !important;
        color: #000000 !important;
    }
}
</style>
@endsection
