<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Holiday;
use App\Models\Service;
use App\Services\HolidayService;
use App\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        protected HolidayService $holidayService,
        protected QuotaService $quotaService
    ) {}

    /**
     * Show the registration / booking page (Slide 2).
     */
    public function create(Request $request): View
    {
        $services = Service::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Selected service from query parameter (e.g. ?service=ktp-el)
        $selectedSlug = $request->query('service');
        $selectedService = $services->firstWhere('slug', $selectedSlug) ?? $services->first();

        // Find the first valid upcoming working day (not holiday, not weekend)
        $initialDate = Carbon::today();
        while ($this->holidayService->checkDate($initialDate)['is_holiday']) {
            $initialDate->addDay();
        }

        // Preload holidays for calendar from current month to next 3 months
        $startWindow = Carbon::today()->startOfMonth();
        $endWindow = Carbon::today()->addMonths(4)->endOfMonth();

        $holidays = Holiday::whereBetween('holiday_date', [$startWindow->toDateString(), $endWindow->toDateString()])
            ->get()
            ->keyBy(fn ($item) => $item->holiday_date->format('Y-m-d'))
            ->map(fn ($item) => [
                'name' => $item->name,
                'type' => $item->type,
            ])
            ->toArray();

        // Get initial shifts for the selected service and initial valid date
        $initialShifts = $this->quotaService->getShiftsForDate($selectedService, $initialDate);

        return view('registration.create', compact(
            'services',
            'selectedService',
            'initialDate',
            'holidays',
            'initialShifts'
        ));
    }

    /**
     * AJAX: Get real-time shift quota for a specific service and date.
     */
    public function getShifts(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
        ]);

        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        $data = $this->quotaService->getShiftsForDate($service, $date);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Show the citizen details form and requirements checklist page (Slide 3).
     */
    public function form(Request $request): View
    {
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        $serviceId = $request->query('service_id');
        $dateStr = $request->query('appointment_date');
        $shift = $request->query('shift', 'pagi');

        $selectedService = $services->firstWhere('id', $serviceId) ?? $services->first();

        // Validate date or find default
        $carbonDate = $dateStr ? Carbon::parse($dateStr) : Carbon::today();
        while ($this->holidayService->checkDate($carbonDate)['is_holiday']) {
            $carbonDate->addDay();
        }

        // Get quota details for this shift
        $shiftData = $this->quotaService->getShiftsForDate($selectedService, $carbonDate);
        $currentShift = collect($shiftData['shifts'])->firstWhere('id', $shift) ?? $shiftData['shifts'][0];

        $districts = [
            'Arjawinangun', 'Astanajapura', 'Babakan', 'Beber', 'Ciledug', 'Ciwaringin',
            'Depok', 'Dukupuntang', 'Gebang', 'Gegesik', 'Gempol', 'Greged',
            'Gunung Jati', 'Jamblang', 'Kaliwedi', 'Kapetakan', 'Karangsembung', 'Karangwareng',
            'Kedawung', 'Klangenan', 'Lemahabang', 'Losari', 'Mundu', 'Pabedilan',
            'Pabuaran', 'Palimanan', 'Pangenan', 'Panguragan', 'Pasaleman', 'Plered',
            'Plumbon', 'Sedong', 'Sumber', 'Suranenggala', 'Susukan', 'Susukan Lebak',
            'Talun', 'Tengahtani', 'Waled', 'Weru',
        ];

        return view('registration.form', compact(
            'services',
            'selectedService',
            'carbonDate',
            'currentShift',
            'shift',
            'districts'
        ));
    }

    /**
     * Store new appointment booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'shift' => 'required|in:pagi,siang',
            'full_name' => 'required|string|min:3|max:100',
            'nik' => ['required', 'string', 'digits:16', 'regex:/^[0-9]{16}$/'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'district' => 'required|string|max:50',
            'address' => 'required|string|min:5|max:300',
            'email' => 'nullable|email|max:100',
            'notes' => 'nullable|string|max:500',
        ], [
            'nik.digits' => 'Nomor Induk Kependudukan (NIK) harus berjumlah tepat 16 digit angka.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'phone.regex' => 'Nomor WhatsApp / HP tidak valid.',
            'appointment_date.after_or_equal' => 'Tanggal kunjungan tidak boleh di masa lampau.',
            'district.required' => 'Pilih Kecamatan domisili Anda di Kabupaten Cirebon.',
            'address.required' => 'Alamat domisili lengkap wajib diisi.',
            'address.min' => 'Alamat domisili minimal 5 karakter (Desa/Kelurahan, RT/RW).',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $date = Carbon::parse($validated['appointment_date']);

        // 1. Holiday & weekend check
        $holidayCheck = $this->holidayService->checkDate($date);
        if ($holidayCheck['is_holiday']) {
            return back()
                ->withInput()
                ->withErrors([
                    'appointment_date' => "Tanggal {$date->format('d/m/Y')} tidak dapat dipilih karena {$holidayCheck['reason']} (LIBUR).",
                ]);
        }

        // 2. Prevent duplicate active booking on the same date for the same NIK
        $existing = Appointment::where('nik', $validated['nik'])
            ->whereDate('appointment_date', $date->toDateString())
            ->whereIn('status', ['confirmed', 'checked_in', 'serving'])
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors([
                    'nik' => "NIK {$validated['nik']} sudah terdaftar pada tanggal tersebut dengan nomor tiket {$existing->ticket_number}.",
                ]);
        }

        // 3. Atomically check quota & insert appointment
        $appointment = DB::transaction(function () use ($service, $date, $validated) {
            $shift = $validated['shift'];
            $totalQuota = match ($shift) {
                'pagi' => $service->quota_morning ?? 50,
                'siang' => $service->quota_afternoon ?? 50,
            };

            $currentBooked = Appointment::where('service_id', $service->id)
                ->whereDate('appointment_date', $date->toDateString())
                ->where('shift', $shift)
                ->whereIn('status', ['confirmed', 'checked_in', 'serving', 'completed'])
                ->lockForUpdate()
                ->count();

            if ($currentBooked >= $totalQuota) {
                throw new \Exception('Mohon maaf, kuota untuk Shift '.ucfirst($shift).' pada tanggal terpilih sudah penuh.');
            }

            // Generate unique queue number (Contoh: CAPIL-CBR-A012)
            $shiftPrefix = match ($shift) {
                'pagi' => 'A',
                'siang' => 'B',
            };
            $queueNumber = sprintf('CAPIL-CBR-%s%03d', $shiftPrefix, $currentBooked + 1);

            // Generate ticket reference number: e.g. TKT-KTP-20261002-A012
            $ticketNumber = sprintf(
                'TKT-%s-%s-%s%03d',
                strtoupper(substr($service->slug, 0, 3)),
                $date->format('Ymd'),
                $shiftPrefix,
                $currentBooked + 1
            );

            return Appointment::create([
                'ticket_number' => $ticketNumber,
                'queue_number' => $queueNumber,
                'service_id' => $service->id,
                'nik' => $validated['nik'],
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'address' => 'Kecamatan '.$validated['district'].', '.$validated['address'],
                'email' => $validated['email'] ?? null,
                'appointment_date' => $date->toDateString(),
                'shift' => $shift,
                'status' => 'confirmed',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('ticket.show', ['ticketNumber' => $appointment->ticket_number])
            ->with('success', 'Pendaftaran antrean online berhasil! Simpan bukti tiket Anda di bawah ini.');
    }

    /**
     * Show ticket confirmation & QR view (Slide 4).
     */
    public function showTicket(string $ticketNumber): View
    {
        $appointment = Appointment::with('service')
            ->where('ticket_number', $ticketNumber)
            ->orWhere('queue_number', $ticketNumber)
            ->firstOrFail();

        $shiftTimes = [
            'pagi' => '08:00 - 12:00 WIB',
            'siang' => '13:00 - 15:00 WIB',
        ];

        // Required 15 minutes before shift starts
        $shiftArrivalTimes = [
            'pagi' => '07:45 WIB',
            'siang' => '12:45 WIB',
        ];

        return view('registration.ticket', compact('appointment', 'shiftTimes', 'shiftArrivalTimes'));
    }
}
