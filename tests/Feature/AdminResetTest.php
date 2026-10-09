<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\QuotaService;
use Carbon\Carbon;
use Database\Seeders\DisdukcapilSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DisdukcapilSeeder::class);
        $this->withSession(['admin_authenticated' => true]);
    }

    /**
     * Test admin dashboard page loads successfully with all monitoring & reset UI elements.
     */
    public function test_admin_dashboard_displays_all_required_elements(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Header & Identity
        $response->assertSee('Panel Administrator');
        $response->assertSee('Disdukcapil Kabupaten Cirebon');
        $response->assertSee('kabcirebon.png');

        // Reset system button & modal elements
        $response->assertSee('RESET DATA SISTEM');
        $response->assertSee('Konfirmasi Reset Sistem Antrean');
        $response->assertSee('Ketik RESET di sini...');
        $response->assertSee('Ya, Eksekusi Reset Sekarang');

        // Monitoring Sections
        $response->assertSee('Monitoring Kuota Layanan Real-Time');
        $response->assertSee('Daftar Antrean Terdaftar');

        // 4 Main Service Card Labels
        $response->assertSee('PELAYAN ADMINDUK');
        $response->assertSee('UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK');
        $response->assertSee('KONSULTASI / PENGADUAN');
        $response->assertSee('LOKET PRIORITAS');
    }

    /**
     * Test admin dashboard correctly reads and filters by selected date.
     */
    public function test_admin_dashboard_reads_selected_date_filter(): void
    {
        $response = $this->get(route('admin.dashboard', ['date' => '2026-10-07']));

        $response->assertStatus(200);
        $response->assertSee('Rabu, 7 Oktober 2026');
        $response->assertSee('value="2026-10-07"', false);
    }

    /**
     * Test resetting system clears appointments, resets queue numbers to initial (0),
     * and restores quota to 100% capacity.
     */
    public function test_admin_reset_clears_all_appointments_and_restores_quota(): void
    {
        $service = Service::firstOrFail();
        $today = Carbon::today()->toDateString();

        // Create initial appointments
        Appointment::create([
            'ticket_number' => 'TKT-TEST-001',
            'queue_number' => 'CAPIL-CBR-A001',
            'service_id' => $service->id,
            'nik' => '3209111122223333',
            'full_name' => 'Warga Pertama',
            'phone' => '081234567890',
            'address' => 'Sumber, Cirebon',
            'appointment_date' => $today,
            'shift' => 'pagi',
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'ticket_number' => 'TKT-TEST-002',
            'queue_number' => 'CAPIL-CBR-A002',
            'service_id' => $service->id,
            'nik' => '3209111122224444',
            'full_name' => 'Warga Kedua',
            'phone' => '081234567891',
            'address' => 'Sumber, Cirebon',
            'appointment_date' => $today,
            'shift' => 'pagi',
            'status' => 'serving',
        ]);

        $this->assertGreaterThan(0, Appointment::count());

        // Perform system reset via POST
        $response = $this->post(route('admin.reset'), [
            'scope' => 'all',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('system_reset', true);

        // Verify all appointments are cleared
        $this->assertEquals(0, Appointment::count());

        // Verify quota is restored to 100% capacity
        $quotaService = app(QuotaService::class);
        $shiftsData = $quotaService->getShiftsForDate($service, $today);
        $morningShift = collect($shiftsData['shifts'])->firstWhere('id', 'pagi');

        $this->assertEquals(0, $morningShift['booked_count']);
        $this->assertEquals($morningShift['total_quota'], $morningShift['remaining_quota']);
        $this->assertFalse($morningShift['is_full']);

        // Verify subsequent booking begins again from 001
        $nextResponse = $this->post(route('registration.store'), [
            'service_id' => $service->id,
            'appointment_date' => Carbon::now()->addWeekday()->toDateString(),
            'shift' => 'pagi',
            'nik' => '3209555566667777',
            'full_name' => 'Warga Baru Pasca Reset',
            'phone' => '081987654321',
            'district' => 'Sumber',
            'address' => 'Jl. Sunan Drajat No. 1',
        ]);

        $newAppointment = Appointment::where('nik', '3209555566667777')->first();
        $this->assertNotNull($newAppointment);
        $this->assertEquals('CAPIL-CBR-A001', $newAppointment->queue_number);
    }

    /**
     * Test resetting system via JSON request (used by interactive Alpine.js modal).
     */
    public function test_admin_reset_via_json_request(): void
    {
        $service = Service::firstOrFail();

        Appointment::create([
            'ticket_number' => 'TKT-TEST-JSON-001',
            'queue_number' => 'CAPIL-CBR-A001',
            'service_id' => $service->id,
            'nik' => '3209111122223333',
            'full_name' => 'Warga JSON',
            'phone' => '081234567890',
            'address' => 'Sumber, Cirebon',
            'appointment_date' => Carbon::today()->toDateString(),
            'shift' => 'pagi',
            'status' => 'confirmed',
        ]);

        $response = $this->postJson(route('admin.reset'), [
            'scope' => 'all',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'scope' => 'all',
            'metrics' => [
                'totalSelectedDate' => 0,
                'totalAllTime' => 0,
                'totalBookedAll' => 0,
            ],
        ]);

        $this->assertEquals(0, Appointment::count());

        // Verify all 4 service cards return 0 booked and 100% capacity restored
        $serviceQuotas = $response->json('serviceQuotas');
        $this->assertNotEmpty($serviceQuotas);
        foreach ($serviceQuotas as $srv) {
            $this->assertEquals(0, $srv['booked']);
            $this->assertEquals($srv['capacity'], $srv['remaining']);
            $this->assertEquals(0, $srv['percent']);
            foreach ($srv['shifts'] as $sh) {
                $this->assertEquals(0, $sh['booked_count']);
                $this->assertEquals($sh['total_quota'], $sh['remaining_quota']);
                $this->assertFalse($sh['is_full']);
            }
        }
    }

    /**
     * Test daily scope reset clears appointments on the target selected date and restores quota.
     */
    public function test_admin_reset_daily_scope(): void
    {
        $service = Service::firstOrFail();
        $targetDate = Carbon::today()->addDay()->toDateString();

        Appointment::create([
            'ticket_number' => 'TKT-TEST-DAILY-001',
            'queue_number' => 'CAPIL-CBR-A001',
            'service_id' => $service->id,
            'nik' => '3209111122223333',
            'full_name' => 'Warga Harian',
            'phone' => '081234567890',
            'address' => 'Sumber, Cirebon',
            'appointment_date' => $targetDate,
            'shift' => 'pagi',
            'status' => 'confirmed',
        ]);

        $this->assertGreaterThan(0, Appointment::whereDate('appointment_date', $targetDate)->count());

        $response = $this->postJson(route('admin.reset'), [
            'scope' => 'today',
            'date' => $targetDate,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('metrics.totalSelectedDate', 0);
        $this->assertEquals(0, Appointment::whereDate('appointment_date', $targetDate)->count());
    }

    /**
     * Test updating appointment status from the admin panel.
     */
    public function test_admin_can_update_appointment_status(): void
    {
        $service = Service::firstOrFail();

        $appointment = Appointment::create([
            'ticket_number' => 'TKT-STATUS-001',
            'queue_number' => 'CAPIL-CBR-A001',
            'service_id' => $service->id,
            'nik' => '3209111122223333',
            'full_name' => 'Warga Status',
            'phone' => '081234567890',
            'address' => 'Sumber, Cirebon',
            'appointment_date' => Carbon::today()->toDateString(),
            'shift' => 'pagi',
            'status' => 'confirmed',
        ]);

        $response = $this->patchJson(route('admin.appointments.status', $appointment), [
            'status' => 'serving',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'new_status' => 'serving',
        ]);

        $this->assertEquals('serving', $appointment->fresh()->status);
    }

    /**
     * Test admin links are completely hidden from public pages.
     */
    public function test_admin_links_are_hidden_from_public_home_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Panel Admin');
        $response->assertDontSee('Panel Administrator');
        $response->assertDontSee(route('admin.dashboard'));
    }

    /**
     * Test hidden /login-admin route displays login form.
     */
    public function test_login_admin_hidden_route_displays_login_page(): void
    {
        $this->flushSession();
        $response = $this->withSession(['admin_authenticated' => false])->get(route('login-admin'));

        $response->assertStatus(200);
        $response->assertSee('Autentikasi Administrator');
        $response->assertSee('Kata Sandi Admin');
    }

    /**
     * Test unauthenticated access to /admin redirects to login.
     */
    public function test_unauthenticated_access_to_admin_redirects_to_login(): void
    {
        $this->flushSession();
        $response = $this->withSession(['admin_authenticated' => false])->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Test admin can authenticate with correct password and access dashboard.
     */
    public function test_admin_can_login_with_correct_password(): void
    {
        $this->flushSession();
        $response = $this->withSession(['admin_authenticated' => false])->post(route('admin.login.submit'), [
            'password' => env('ADMIN_PASSWORD', 'admin123'),
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_authenticated'));
    }
}
