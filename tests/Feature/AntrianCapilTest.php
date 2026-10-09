<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Holiday;
use App\Models\Service;
use Carbon\Carbon;
use Database\Seeders\DisdukcapilSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntrianCapilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DisdukcapilSeeder::class);
    }

    /**
     * Test Slide 1: Halaman Beranda (Header, Identitas, Hero, CTA, Kotak Pengumuman).
     */
    public function test_home_page_displays_slide_1_requirements(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Header & Identitas
        $response->assertSee('Disdukcapil Kabupaten Cirebon');
        $response->assertSee('kabcirebon.png');
        $response->assertSee('Beranda');
        $response->assertSee('Cek Kuota');
        $response->assertSee('Syarat Berkas');
        $response->assertSee('Cek Tiket');

        // Hero Section: Sapaan ramah warga & penjelasan alternatif nomor antrean offline
        $response->assertSee('Sampurasun Warga Kabupaten Cirebon!');
        $response->assertSee('solusi alternatif resmi');
        $response->assertSee('kehabisan nomor antrean offline');

        // CTA Besar Mencolok
        $response->assertSee('Daftar Antrean Online Sekarang');

        // Informasi Darurat / Pengumuman status operasional
        $response->assertSee('Status Hari Ini:');

        // 4 Main Service Card Labels
        $response->assertSee('PELAYAN ADMINDUK');
        $response->assertSee('UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK');
        $response->assertSee('KONSULTASI / PENGADUAN');
        $response->assertSee('LOKET PRIORITAS');
    }

    /**
     * Test Slide 2: Halaman Pilih Layanan & Jadwal (Pilihan Layanan, Kalender Libur, Shift).
     */
    public function test_registration_page_displays_slide_2_requirements(): void
    {
        $response = $this->get(route('registration.create'));

        $response->assertStatus(200);

        // Pilihan Layanan Kependudukan
        $response->assertSee('Pembuatan KTP-el');
        $response->assertSee('Kartu Keluarga (KK)');
        $response->assertSee('Akta Kelahiran');
        $response->assertSee('Surat Pindah Datang');

        // 4 Main Service Card Labels
        $response->assertSee('PELAYAN ADMINDUK');
        $response->assertSee('UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK');
        $response->assertSee('KONSULTASI / PENGADUAN');
        $response->assertSee('LOKET PRIORITAS');

        // Sistem Kalender Interaktif & Validasi Hari Libur
        $response->assertSee('Sistem Kalender Interaktif');
        $response->assertSee('Validasi Hari Libur');
        $response->assertSee('LIBUR');

        // Manajemen Shift & Kuota Real-Time
        $response->assertSee('Manajemen Shift');
        $response->assertSee('Kuota Real-Time');
        $response->assertSee('Shift Pagi');
        $response->assertSee('08:00 - 12:00 WIB');
        $response->assertSee('Shift Siang');
        $response->assertSee('13:00 - 15:00 WIB');
        $response->assertDontSee('Shift Sore');
    }

    /**
     * Test Shift API returns real-time quota and detects full shifts.
     */
    public function test_shift_api_returns_realtime_quota_and_disabled_state(): void
    {
        $ktpService = Service::where('slug', 'ktp-el')->firstOrFail();

        // Get date where appointments were seeded
        $targetDate = Appointment::where('service_id', $ktpService->id)->value('appointment_date') ?? Carbon::today();
        $targetDateStr = $targetDate instanceof Carbon ? $targetDate->toDateString() : Carbon::parse($targetDate)->toDateString();

        $response = $this->getJson(route('registration.shifts', [
            'service_id' => $ktpService->id,
            'date' => $targetDateStr,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'service_id',
                'service_name',
                'date',
                'shifts' => [
                    '*' => [
                        'id',
                        'name',
                        'time',
                        'total_quota',
                        'booked_count',
                        'remaining_quota',
                        'is_full',
                        'is_disabled',
                        'status_label',
                    ],
                ],
            ],
        ]);

        $shifts = $response->json('data.shifts');
        $this->assertCount(2, $shifts);

        // In DisdukcapilSeeder, shift siang has 50 booked = Kuota Penuh
        $siangShift = collect($shifts)->firstWhere('id', 'siang');
        $this->assertTrue($siangShift['is_full']);
        $this->assertTrue($siangShift['is_disabled']);
        $this->assertEquals('Kuota Penuh', $siangShift['status_label']);

        // Shift pagi has 35 booked / 50 total = 15 remaining
        $pagiShift = collect($shifts)->firstWhere('id', 'pagi');
        $this->assertEquals(15, $pagiShift['remaining_quota']);
        $this->assertFalse($pagiShift['is_full']);
        $this->assertEquals('Sisa Kuota: 15 / 50', $pagiShift['status_label']);
    }

    /**
     * Test Booking validation rejects weekends and holidays.
     */
    public function test_booking_rejects_weekend(): void
    {
        $service = Service::firstOrFail();
        $nextSunday = Carbon::now()->next(Carbon::SUNDAY);

        $response = $this->post(route('registration.store'), [
            'service_id' => $service->id,
            'appointment_date' => $nextSunday->toDateString(),
            'shift' => 'pagi',
            'nik' => '3209123456780001',
            'full_name' => 'Warga Cirebon',
            'phone' => '081234567890',
            'district' => 'Sumber',
            'address' => 'Jl. Sunan Kalijaga No. 10',
        ]);

        $response->assertSessionHasErrors('appointment_date');
    }

    /**
     * Test Successful Booking and ticket generation.
     */
    public function test_successful_booking_creates_appointment_and_redirects_to_ticket(): void
    {
        $service = Service::where('slug', 'kartu-keluarga')->firstOrFail();

        // Find next valid weekday
        $targetDate = Carbon::today()->addDays(3);
        while ($targetDate->isWeekend() || Holiday::where('holiday_date', $targetDate->toDateString())->exists()) {
            $targetDate->addDay();
        }

        $nik = '3209998877660001';
        $response = $this->post(route('registration.store'), [
            'service_id' => $service->id,
            'appointment_date' => $targetDate->toDateString(),
            'shift' => 'pagi',
            'nik' => $nik,
            'full_name' => 'Ahmad Fauzi',
            'phone' => '081987654321',
            'district' => 'Sumber',
            'address' => 'Jl. Pangeran Drajat No. 10',
            'notes' => 'Penerbitan KK baru barcode TTE',
        ]);

        $appointment = Appointment::where('nik', $nik)->first();
        $this->assertNotNull($appointment);
        $this->assertEquals('confirmed', $appointment->status);
        $this->assertStringContainsString('TKT-KAR', $appointment->ticket_number);

        $response->assertRedirect(route('ticket.show', ['ticketNumber' => $appointment->ticket_number]));

        // Check ticket show page
        $ticketResponse = $this->get(route('ticket.show', ['ticketNumber' => $appointment->ticket_number]));
        $ticketResponse->assertStatus(200);
        $ticketResponse->assertSee($appointment->queue_number);
        $ticketResponse->assertSee('Ahmad Fauzi');
    }

    /**
     * Test Slide 4: Halaman Tiket Digital / Bukti Pendaftaran (Semua Spesifikasi Lengkap).
     */
    public function test_slide_4_ticket_page_fulfills_all_requirements(): void
    {
        $service = Service::where('slug', 'ktp-el')->firstOrFail();

        // Create an appointment with example queue number format CAPIL-CBR-A012
        $appointment = Appointment::create([
            'ticket_number' => 'TKT-KTP-20261015-A012',
            'queue_number' => 'CAPIL-CBR-A012',
            'service_id' => $service->id,
            'nik' => '3209123456780012',
            'full_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Kecamatan Sumber, Jl. Pangeran Drajat No. 15',
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'shift' => 'pagi',
            'status' => 'confirmed',
        ]);

        $response = $this->get(route('ticket.show', ['ticketNumber' => $appointment->ticket_number]));

        $response->assertStatus(200);

        // 1. Nomor Antrean Unik (Contoh: CAPIL-CBR-A012)
        $response->assertSee('CAPIL-CBR-A012');

        // 2. Nama Pemohon dan NIK (disensor sebagian untuk privasi)
        $response->assertSee('Budi Santoso');
        $response->assertSee('320912******0012');
        $this->assertFalse(str_contains($response->getContent(), '3209123456780012'));

        // 3. Layanan yang dipilih, Tanggal Kunjungan, dan Shift yang diambil
        $response->assertSee($service->name);
        $response->assertSee('Shift Pagi');
        $response->assertSee('08:00 - 12:00 WIB');

        // 4. QR Code Dummy/Aktif sebagai kode validasi petugas
        $response->assertSee('KODE VALIDASI PETUGAS');
        $response->assertSee('api.qrserver.com');

        // 5. Peringatan/Reminder Wajib
        $response->assertSee('Harap hadir 15 menit sebelum shift dimulai dengan membawa dokumen fisik lengkap sesuai persyaratan.');

        // 6. Tombol Aksi: Download Tiket (PDF) dan Cetak Tiket
        $response->assertSee('Download Tiket (PDF)');
        $response->assertSee('Cetak Tiket');

        // 7. Optimasi Print: Barcode & Slip Ringkas (Hanya 1 Lembar)
        $response->assertSee('@media print', false);
        $response->assertSee('Visual Barcode Representation');
        $response->assertSee('PRINT-ONLY COMPACT SLIP DETAILS');
        $response->assertSee('print:hidden');
    }

    /**
     * Test Slide 5: Halaman Cek Status / Cari Tiket (Pencarian NIK, WA, dan Local Storage).
     */
    public function test_slide_5_search_page_displays_all_requirements(): void
    {
        $response = $this->get(route('ticket.search'));

        $response->assertStatus(200);

        // Header & Judul
        $response->assertSee('Cek Status & Cari Tiket');
        $response->assertSee('Slide 5');

        // Input pencarian NIK & WhatsApp
        $response->assertSee('Cari via NIK');
        $response->assertSee('No. WhatsApp');

        // Local Storage / State detector element
        $response->assertSee('Tiket Aktif Ditemukan di Ponsel / Perangkat Ini');
        $response->assertSee('Local Storage');
    }

    /**
     * Test Slide 5: Search API supports NIK and WhatsApp phone number lookup.
     */
    public function test_slide_5_search_api_supports_nik_and_whatsapp_search(): void
    {
        $service = Service::where('slug', 'kartu-keluarga')->firstOrFail();

        $appointment = Appointment::create([
            'ticket_number' => 'TKT-KK-20261020-B005',
            'queue_number' => 'CAPIL-CBR-B005',
            'service_id' => $service->id,
            'nik' => '3209887766550001',
            'full_name' => 'Siti Aminah',
            'phone' => '081298765432',
            'address' => 'Kecamatan Kedawung',
            'appointment_date' => Carbon::tomorrow()->toDateString(),
            'shift' => 'siang',
            'status' => 'confirmed',
        ]);

        // Search by NIK
        $nikResponse = $this->postJson(route('ticket.check'), [
            'keyword' => '3209887766550001',
        ]);
        $nikResponse->assertStatus(200);
        $nikResponse->assertJsonPath('data.full_name', 'Siti Aminah');
        $nikResponse->assertJsonPath('data.queue_number', 'CAPIL-CBR-B005');

        // Search by WhatsApp Number (08...)
        $phoneResponse = $this->postJson(route('ticket.check'), [
            'keyword' => '081298765432',
        ]);
        $phoneResponse->assertStatus(200);
        $phoneResponse->assertJsonPath('data.full_name', 'Siti Aminah');

        // Search by normalized WhatsApp Number with 62...
        $phone62Response = $this->postJson(route('ticket.check'), [
            'keyword' => '6281298765432',
        ]);
        $phone62Response->assertStatus(200);
        $phone62Response->assertJsonPath('data.full_name', 'Siti Aminah');

        // Non-existent search returns 404
        $notFound = $this->postJson(route('ticket.check'), [
            'keyword' => '3209000000000000',
        ]);
        $notFound->assertStatus(404);
        $notFound->assertJsonPath('status', 'error');
    }
}
