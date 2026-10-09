<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;

class QuotaService
{
    public function __construct(
        protected HolidayService $holidayService
    ) {}

    /**
     * Get shifts with real-time remaining quota for a given service and date.
     */
    public function getShiftsForDate(int|Service $service, string|Carbon $date): array
    {
        $serviceModel = is_numeric($service) ? Service::findOrFail($service) : $service;
        $carbonDate = is_string($date) ? Carbon::parse($date) : $date;
        $dateString = $carbonDate->toDateString();

        $holidayCheck = $this->holidayService->checkDate($carbonDate);
        $isPastDate = $carbonDate->lt(Carbon::today());
        $isHolidayOrPast = $holidayCheck['is_holiday'] || $isPastDate;

        $shiftsConfig = [
            'pagi' => [
                'id' => 'pagi',
                'name' => 'Shift Pagi',
                'time' => '08:00 - 12:00 WIB',
                'total_quota' => $serviceModel->getQuotaForShift('pagi'),
            ],
            'siang' => [
                'id' => 'siang',
                'name' => 'Shift Siang',
                'time' => '13:00 - 15:00 WIB',
                'total_quota' => $serviceModel->getQuotaForShift('siang'),
            ],
        ];

        // Fetch booked counts for this service and date
        $bookedCounts = Appointment::where('service_id', $serviceModel->id)
            ->whereDate('appointment_date', $dateString)
            ->whereIn('status', ['confirmed', 'checked_in', 'serving', 'completed'])
            ->selectRaw('shift, COUNT(*) as count')
            ->groupBy('shift')
            ->pluck('count', 'shift')
            ->toArray();

        $result = [];
        foreach ($shiftsConfig as $key => $config) {
            $booked = $bookedCounts[$key] ?? 0;
            $total = $config['total_quota'];
            $remaining = max(0, $total - $booked);
            $isFull = ($remaining <= 0);

            $isDisabled = $isHolidayOrPast || $isFull;

            $statusLabel = $isFull
                ? 'Kuota Penuh'
                : "Sisa Kuota: {$remaining} / {$total}";

            $result[] = [
                'id' => $key,
                'name' => $config['name'],
                'time' => $config['time'],
                'total_quota' => $total,
                'booked_count' => $booked,
                'remaining_quota' => $remaining,
                'is_full' => $isFull,
                'is_disabled' => $isDisabled,
                'status_label' => $statusLabel,
                'percentage' => round(($booked / max(1, $total)) * 100),
            ];
        }

        return [
            'service_id' => $serviceModel->id,
            'service_name' => $serviceModel->name,
            'date' => $dateString,
            'formatted_date' => $carbonDate->locale('id')->isoFormat('dddd, D MMMM Y'),
            'is_holiday' => $holidayCheck['is_holiday'],
            'holiday_reason' => $holidayCheck['reason'],
            'shifts' => $result,
        ];
    }
}
