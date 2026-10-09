<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\HolidayService;
use App\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected HolidayService $holidayService,
        protected QuotaService $quotaService
    ) {}

    /**
     * Display the Home Page (Slide 1).
     */
    public function index(): View
    {
        $services = Service::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $operationalStatus = $this->holidayService->getTodayOperationalStatus();

        // Calculate summary quota for today / next working day
        $today = Carbon::today();
        $targetDate = $today;
        if ($this->holidayService->checkDate($targetDate)['is_holiday']) {
            $nextDate = $targetDate->copy()->addDay();
            while ($this->holidayService->checkDate($nextDate)['is_holiday']) {
                $nextDate->addDay();
            }
            $targetDate = $nextDate;
        }

        $quotaSummary = [];
        $totalQuotaAvailable = 0;
        $totalQuotaBooked = 0;

        foreach ($services as $service) {
            $shiftData = $this->quotaService->getShiftsForDate($service, $targetDate);
            $serviceTotal = 0;
            $serviceRemaining = 0;
            foreach ($shiftData['shifts'] as $shift) {
                $serviceTotal += $shift['total_quota'];
                $serviceRemaining += $shift['remaining_quota'];
                $totalQuotaAvailable += $shift['remaining_quota'];
                $totalQuotaBooked += $shift['booked_count'];
            }
            $quotaSummary[$service->id] = [
                'service' => $service,
                'total' => $serviceTotal,
                'remaining' => $serviceRemaining,
                'booked' => $serviceTotal - $serviceRemaining,
                'shifts' => $shiftData['shifts'],
            ];
        }

        $announcements = [
            [
                'id' => 1,
                'type' => 'important',
                'title' => 'Solusi Resmi Kuota Offline Habis',
                'badge' => 'Penting',
                'badge_color' => 'amber',
                'content' => 'Bagi masyarakat yang kehabisan nomor antrean offline di loket Disdukcapil Sumber (Jl. Sunan Muria Komplek Pemda No. 04), kuota pendaftaran dialihkan secara resmi ke antrean online ini. Pastikan Anda mendaftar shift sebelum hadir ke kantor.',
                'date' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
            ],
            [
                'id' => 2,
                'type' => 'info',
                'title' => 'Ketersediaan Blangko KTP-el Aman & Cukup',
                'badge' => 'Informasi',
                'badge_color' => 'emerald',
                'content' => 'Stok blangko KTP-el di Disdukcapil Kabupaten Cirebon saat ini dalam kondisi sangat mencukupi (tersedia 2.500+ keping). Cetak fisik dapat ditunggu langsung sesuai jam shift.',
                'date' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
            ],
            [
                'id' => 3,
                'type' => 'notice',
                'title' => 'Ketentuan Berkas Asli & Kehadiran',
                'badge' => 'Perhatian',
                'badge_color' => 'blue',
                'content' => 'Harap hadir 15 menit sebelum shift dimulai dengan membawa dokumen asli dan bukti tiket QR Code (cetak atau tangkapan layar di ponsel). Keterlambatan lebih dari 30 menit memerlukan pendaftaran ulang.',
                'date' => Carbon::now()->locale('id')->isoFormat('D MMMM Y'),
            ],
        ];

        return view('home', compact(
            'services',
            'operationalStatus',
            'quotaSummary',
            'totalQuotaAvailable',
            'totalQuotaBooked',
            'announcements',
            'targetDate'
        ));
    }

    /**
     * AJAX endpoint to check quota by date and service.
     */
    public function checkQuota(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
        ]);

        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        $shiftData = $this->quotaService->getShiftsForDate($service, $date);

        return response()->json([
            'status' => 'success',
            'data' => $shiftData,
        ]);
    }

    /**
     * Show the ticket search and status tracking page (Slide 5).
     */
    public function searchTicketPage(Request $request): View
    {
        $prefilled = trim($request->query('q', $request->query('nik', $request->query('phone', ''))));

        return view('ticket.search', compact('prefilled'));
    }

    /**
     * AJAX / Post endpoint to check ticket / booking status (Supports NIK, WhatsApp / Phone, and Ticket Code).
     */
    public function checkTicket(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:3',
        ]);

        $keyword = trim($request->keyword);

        // Normalize numbers for phone / NIK matching
        $cleanDigits = preg_replace('/[^0-9]/', '', $keyword);
        $phoneVariants = [];
        if (! empty($cleanDigits)) {
            $phoneVariants[] = $cleanDigits;
            if (str_starts_with($cleanDigits, '62')) {
                $phoneVariants[] = '0'.substr($cleanDigits, 2);
            } elseif (str_starts_with($cleanDigits, '0')) {
                $phoneVariants[] = '62'.substr($cleanDigits, 1);
                $phoneVariants[] = substr($cleanDigits, 1); // e.g. 812...
            } elseif (str_starts_with($cleanDigits, '8')) {
                $phoneVariants[] = '0'.$cleanDigits;
                $phoneVariants[] = '62'.$cleanDigits;
            }
        }

        $appointments = Appointment::with('service')
            ->where(function ($query) use ($keyword, $cleanDigits, $phoneVariants) {
                $query->where('ticket_number', 'LIKE', "%{$keyword}%")
                    ->orWhere('queue_number', 'LIKE', "%{$keyword}%");

                if (! empty($cleanDigits)) {
                    $query->orWhere('nik', $cleanDigits)
                        ->orWhere('nik', 'LIKE', "%{$cleanDigits}%");

                    foreach ($phoneVariants as $pv) {
                        $query->orWhere('phone', 'LIKE', "%{$pv}%");
                    }
                }
            })
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tiket tidak ditemukan. Mohon periksa kembali NIK atau Nomor WhatsApp yang Anda masukkan saat mendaftar.',
            ], 404);
        }

        $shiftLabels = [
            'pagi' => 'Shift Pagi (08:00 - 12:00 WIB)',
            'siang' => 'Shift Siang (13:00 - 15:00 WIB)',
        ];

        $shiftArrivalTimes = [
            'pagi' => '07:45 WIB',
            'siang' => '12:45 WIB',
        ];

        $formattedTickets = $appointments->map(function ($app) use ($shiftLabels, $shiftArrivalTimes) {
            return [
                'ticket_number' => $app->ticket_number,
                'queue_number' => $app->formatted_queue_number ?? $app->queue_number,
                'service_name' => $app->service->name,
                'full_name' => $app->full_name,
                'nik_masked' => $app->masked_nik ?? (substr($app->nik, 0, 6).'******'.substr($app->nik, -4)),
                'phone' => $app->phone,
                'appointment_date' => $app->appointment_date->locale('id')->isoFormat('dddd, D MMMM Y'),
                'appointment_date_raw' => $app->appointment_date->format('Y-m-d'),
                'shift' => $app->shift,
                'shift_name' => $shiftLabels[$app->shift] ?? $app->shift,
                'arrival_time' => $shiftArrivalTimes[$app->shift] ?? '15 Menit Sebelum Shift',
                'status' => $app->status,
                'status_badge' => match ($app->status) {
                    'confirmed' => 'Terdaftar / Menunggu',
                    'checked_in' => 'Sudah Hadir di Loket',
                    'serving' => 'Sedang Diproses',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                    default => $app->status,
                },
                'ticket_url' => route('ticket.show', ['ticketNumber' => $app->ticket_number]),
                'is_active' => in_array($app->status, ['confirmed', 'checked_in', 'serving']),
                'is_today' => $app->appointment_date->isToday(),
                'created_at' => $app->created_at ? $app->created_at->format('d/m/Y H:i') : null,
            ];
        });

        // The primary result is the latest active ticket, or latest ticket overall
        $primary = $formattedTickets->firstWhere('is_active', true) ?? $formattedTickets->first();

        return response()->json([
            'status' => 'success',
            'count' => $formattedTickets->count(),
            'data' => $primary,
            'tickets' => $formattedTickets->values()->all(),
        ]);
    }
}
