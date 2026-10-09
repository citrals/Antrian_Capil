<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    /**
     * Display the Admin Dashboard with Real-Time Queue & Quota Monitoring.
     */
    public function index(Request $request): View
    {
        $selectedDateStr = $request->input('date') ?? $request->query('date');

        if (! empty($selectedDateStr)) {
            try {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDateStr)) {
                    $selectedDate = Carbon::createFromFormat('Y-m-d', $selectedDateStr)->startOfDay();
                } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $selectedDateStr)) {
                    $selectedDate = Carbon::createFromFormat('d/m/Y', $selectedDateStr)->startOfDay();
                } else {
                    $selectedDate = Carbon::parse($selectedDateStr)->startOfDay();
                }
            } catch (\Throwable $e) {
                $selectedDate = Carbon::today();
            }
        } else {
            $selectedDate = Carbon::today();
        }

        $services = Service::where('is_active', true)->orderBy('sort_order')->get();

        // 1. Overall & Selected Date Queue Metrics
        $totalAllTime = Appointment::count();
        $totalSelectedDate = Appointment::whereDate('appointment_date', $selectedDate)->count();
        $waitingCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();
        $servingCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->where('status', 'serving')
            ->count();
        $completedCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->where('status', 'completed')
            ->count();
        $cancelledCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->where('status', 'cancelled')
            ->count();

        // 2. Real-Time Quota per Service for the selected date
        $serviceQuotas = [];
        $totalCapacityAll = 0;
        $totalBookedAll = 0;

        foreach ($services as $srv) {
            $shiftData = $this->quotaService->getShiftsForDate($srv, $selectedDate);
            $serviceCapacity = 0;
            $serviceBooked = 0;

            foreach ($shiftData['shifts'] as $sh) {
                $serviceCapacity += $sh['total_quota'];
                $serviceBooked += $sh['booked_count'];
            }

            $totalCapacityAll += $serviceCapacity;
            $totalBookedAll += $serviceBooked;

            $serviceQuotas[] = [
                'service' => $srv,
                'data' => $shiftData,
                'capacity' => $serviceCapacity,
                'booked' => $serviceBooked,
                'remaining' => max(0, $serviceCapacity - $serviceBooked),
                'percent' => $serviceCapacity > 0 ? round(($serviceBooked / $serviceCapacity) * 100) : 0,
            ];
        }

        // 3. Appointments Table Query with Filters
        $query = Appointment::with('service')
            ->whereDate('appointment_date', $selectedDate);

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->query('service_id'));
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->query('shift'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('queue_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $appointments = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.dashboard', compact(
            'services',
            'selectedDate',
            'totalAllTime',
            'totalSelectedDate',
            'waitingCount',
            'servingCount',
            'completedCount',
            'cancelledCount',
            'serviceQuotas',
            'totalCapacityAll',
            'totalBookedAll',
            'appointments'
        ));
    }

    /**
     * Reset the queue system: clears appointments, resets queue numbering to 0,
     * restores all daily quotas to maximum capacity, and flushes cache.
     */
    public function reset(Request $request): RedirectResponse|JsonResponse
    {
        $scope = $request->input('scope', 'all');
        $selectedDateStr = $request->input('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($selectedDateStr);

        $countBefore = Appointment::count();

        DB::transaction(function () use ($scope, $selectedDate) {
            if ($scope === 'today' || $scope === 'daily') {
                Appointment::whereDate('appointment_date', $selectedDate->toDateString())->delete();
                if ($selectedDate->toDateString() !== Carbon::today()->toDateString()) {
                    Appointment::whereDate('appointment_date', Carbon::today()->toDateString())->delete();
                }
            } else {
                // Total system reset: clear all appointment records
                Appointment::query()->delete();
                try {
                    DB::statement('DELETE FROM sqlite_sequence WHERE name = "appointments"');
                } catch (\Throwable $e) {
                    // Ignore if driver is not SQLite
                }
            }

            // Flush application cache so quota and counters reload fresh
            Cache::flush();
        });

        // 1. Recalculate metrics for selectedDate
        $totalAllTime = Appointment::count();
        $totalSelectedDate = Appointment::whereDate('appointment_date', $selectedDate)->count();
        $waitingCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();
        $servingCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->where('status', 'serving')
            ->count();
        $completedCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->where('status', 'completed')
            ->count();
        $cancelledCount = Appointment::whereDate('appointment_date', $selectedDate)
            ->where('status', 'cancelled')
            ->count();

        // 2. Real-Time Quota per Service for the selected date
        $services = Service::where('is_active', true)->orderBy('sort_order')->get();
        $serviceQuotas = [];
        $totalCapacityAll = 0;
        $totalBookedAll = 0;

        foreach ($services as $srv) {
            $shiftData = $this->quotaService->getShiftsForDate($srv, $selectedDate);
            $serviceCapacity = 0;
            $serviceBooked = 0;

            foreach ($shiftData['shifts'] as $sh) {
                $serviceCapacity += $sh['total_quota'];
                $serviceBooked += $sh['booked_count'];
            }

            $totalCapacityAll += $serviceCapacity;
            $totalBookedAll += $serviceBooked;

            $serviceQuotas[] = [
                'id' => $srv->id,
                'slug' => $srv->slug,
                'name' => $srv->name,
                'capacity' => $serviceCapacity,
                'booked' => $serviceBooked,
                'remaining' => max(0, $serviceCapacity - $serviceBooked),
                'percent' => $serviceCapacity > 0 ? round(($serviceBooked / $serviceCapacity) * 100) : 0,
                'shifts' => $shiftData['shifts'],
            ];
        }

        $message = 'Sistem berhasil direset secara menyeluruh! Seluruh daftar antrean aktif telah dikosongkan ke 0 pemohon, penomoran antrean kembali ke posisi awal (001 untuk pendaftar baru), dan kuota harian seluruh layanan (Shift Pagi & Siang) telah dipulihkan ke kapasitas maksimal (100%).';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'cleared_records' => $countBefore,
                'reset_timestamp' => now()->toIso8601String(),
                'scope' => $scope,
                'metrics' => [
                    'totalSelectedDate' => $totalSelectedDate,
                    'totalAllTime' => $totalAllTime,
                    'waitingCount' => $waitingCount,
                    'servingCount' => $servingCount,
                    'completedCount' => $completedCount,
                    'cancelledCount' => $cancelledCount,
                    'totalBookedAll' => $totalBookedAll,
                    'totalCapacityAll' => $totalCapacityAll,
                    'remainingCapacityAll' => max(0, $totalCapacityAll - $totalBookedAll),
                ],
                'serviceQuotas' => $serviceQuotas,
            ]);
        }

        if ($request->filled('date')) {
            return redirect()->route('admin.dashboard', ['date' => $selectedDate->toDateString()])
                ->with('success', $message)
                ->with('system_reset', true);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', $message)
            ->with('system_reset', true);
    }

    /**
     * Update appointment status (e.g., serving, completed, cancelled).
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,checked_in,serving,completed,cancelled',
        ]);

        $appointment->update([
            'status' => $validated['status'],
        ]);

        $label = match ($validated['status']) {
            'confirmed' => 'Menunggu',
            'checked_in' => 'Hadir di Loket',
            'serving' => 'Sedang Diproses',
            'completed' => 'Selesai Dilayani',
            'cancelled' => 'Dibatalkan',
            default => $validated['status'],
        };

        $message = "Status antrean {$appointment->queue_number} berhasil diperbarui menjadi {$label}.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'queue_number' => $appointment->queue_number,
                'new_status' => $appointment->status,
                'badge' => $appointment->status_badge,
            ]);
        }

        return back()->with('success', $message);
    }
}
