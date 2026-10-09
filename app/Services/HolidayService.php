<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;

class HolidayService
{
    /**
     * Authoritative Indonesian National Holidays & Cuti Bersama (SKB 3 Menteri).
     *
     * @var array<string, array{name: string, type: string}>
     */
    protected array $nationalHolidays = [
        // === TAHUN 2025 ===
        '2025-01-01' => ['name' => 'Tahun Baru 2025 Masehi', 'type' => 'national'],
        '2025-01-27' => ['name' => "Isra Mi'raj Nabi Muhammad SAW", 'type' => 'national'],
        '2025-01-28' => ['name' => 'Cuti Bersama Tahun Baru Imlek 2576 Kongzili', 'type' => 'cuti_bersama'],
        '2025-01-29' => ['name' => 'Tahun Baru Imlek 2576 Kongzili', 'type' => 'national'],
        '2025-03-28' => ['name' => 'Cuti Bersama Hari Suci Nyepi', 'type' => 'cuti_bersama'],
        '2025-03-29' => ['name' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)', 'type' => 'national'],
        '2025-03-31' => ['name' => 'Hari Raya Idul Fitri 1446 Hijriah', 'type' => 'national'],
        '2025-04-01' => ['name' => 'Hari Raya Idul Fitri 1446 Hijriah (Hari Ke-2)', 'type' => 'national'],
        '2025-04-02' => ['name' => 'Cuti Bersama Idul Fitri 1446 Hijriah', 'type' => 'cuti_bersama'],
        '2025-04-03' => ['name' => 'Cuti Bersama Idul Fitri 1446 Hijriah', 'type' => 'cuti_bersama'],
        '2025-04-04' => ['name' => 'Cuti Bersama Idul Fitri 1446 Hijriah', 'type' => 'cuti_bersama'],
        '2025-04-07' => ['name' => 'Cuti Bersama Idul Fitri 1446 Hijriah', 'type' => 'cuti_bersama'],
        '2025-04-18' => ['name' => 'Wafat Yesus Kristus (Jumat Agung)', 'type' => 'national'],
        '2025-04-20' => ['name' => 'Hari Paskah', 'type' => 'national'],
        '2025-05-01' => ['name' => 'Hari Buruh Internasional', 'type' => 'national'],
        '2025-05-12' => ['name' => 'Hari Raya Waisak 2569 BE', 'type' => 'national'],
        '2025-05-13' => ['name' => 'Cuti Bersama Hari Raya Waisak', 'type' => 'cuti_bersama'],
        '2025-05-29' => ['name' => 'Kenaikan Yesus Kristus', 'type' => 'national'],
        '2025-05-30' => ['name' => 'Cuti Bersama Kenaikan Yesus Kristus', 'type' => 'cuti_bersama'],
        '2025-06-01' => ['name' => 'Hari Lahir Pancasila', 'type' => 'national'],
        '2025-06-06' => ['name' => 'Hari Raya Idul Adha 1446 Hijriah', 'type' => 'national'],
        '2025-06-09' => ['name' => 'Cuti Bersama Hari Raya Idul Adha', 'type' => 'cuti_bersama'],
        '2025-06-27' => ['name' => 'Tahun Baru Islam 1447 Hijriah', 'type' => 'national'],
        '2025-08-17' => ['name' => 'Hari Kemerdekaan Republik Indonesia ke-80', 'type' => 'national'],
        '2025-09-05' => ['name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],
        '2025-12-25' => ['name' => 'Hari Raya Natal', 'type' => 'national'],
        '2025-12-26' => ['name' => 'Cuti Bersama Hari Raya Natal', 'type' => 'cuti_bersama'],

        // === TAHUN 2026 ===
        '2026-01-01' => ['name' => 'Tahun Baru 2026 Masehi', 'type' => 'national'],
        '2026-01-16' => ['name' => "Isra Mi'raj Nabi Muhammad SAW", 'type' => 'national'],
        '2026-02-16' => ['name' => 'Cuti Bersama Tahun Baru Imlek 2577 Kongzili', 'type' => 'cuti_bersama'],
        '2026-02-17' => ['name' => 'Tahun Baru Imlek 2577 Kongzili', 'type' => 'national'],
        '2026-03-19' => ['name' => 'Cuti Bersama Hari Suci Nyepi', 'type' => 'cuti_bersama'],
        '2026-03-20' => ['name' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'type' => 'national'],
        '2026-03-21' => ['name' => 'Hari Raya Idul Fitri 1447 Hijriah', 'type' => 'national'],
        '2026-03-22' => ['name' => 'Hari Raya Idul Fitri 1447 Hijriah (Hari Ke-2)', 'type' => 'national'],
        '2026-03-23' => ['name' => 'Cuti Bersama Hari Raya Idul Fitri', 'type' => 'cuti_bersama'],
        '2026-03-24' => ['name' => 'Cuti Bersama Hari Raya Idul Fitri', 'type' => 'cuti_bersama'],
        '2026-03-25' => ['name' => 'Cuti Bersama Hari Raya Idul Fitri', 'type' => 'cuti_bersama'],
        '2026-04-03' => ['name' => 'Wafat Yesus Kristus (Jumat Agung)', 'type' => 'national'],
        '2026-04-05' => ['name' => 'Kebangkitan Yesus Kristus (Paskah)', 'type' => 'national'],
        '2026-05-01' => ['name' => 'Hari Buruh Internasional', 'type' => 'national'],
        '2026-05-14' => ['name' => 'Kenaikan Yesus Kristus', 'type' => 'national'],
        '2026-05-15' => ['name' => 'Cuti Bersama Kenaikan Yesus Kristus', 'type' => 'cuti_bersama'],
        '2026-05-31' => ['name' => 'Hari Raya Waisak 2570 BE', 'type' => 'national'],
        '2026-06-01' => ['name' => 'Hari Lahir Pancasila', 'type' => 'national'],
        '2026-06-02' => ['name' => 'Cuti Bersama Hari Raya Waisak', 'type' => 'cuti_bersama'],
        '2026-06-16' => ['name' => 'Hari Raya Idul Adha 1447 Hijriah', 'type' => 'national'],
        '2026-06-17' => ['name' => 'Cuti Bersama Hari Raya Idul Adha', 'type' => 'cuti_bersama'],
        '2026-07-07' => ['name' => 'Tahun Baru Islam 1448 Hijriah', 'type' => 'national'],
        '2026-08-17' => ['name' => 'Hari Kemerdekaan Republik Indonesia ke-81', 'type' => 'national'],
        '2026-09-15' => ['name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],
        '2026-10-01' => ['name' => 'Hari Kesaktian Pancasila / Libur Layanan', 'type' => 'special'],
        '2026-10-24' => ['name' => 'Hari Jadi Layanan Dukcapil (Libur Khusus)', 'type' => 'special'],
        '2026-12-25' => ['name' => 'Hari Raya Natal', 'type' => 'national'],
        '2026-12-26' => ['name' => 'Cuti Bersama Hari Raya Natal', 'type' => 'cuti_bersama'],

        // === TAHUN 2027 ===
        '2027-01-01' => ['name' => 'Tahun Baru 2027 Masehi', 'type' => 'national'],
        '2027-02-06' => ['name' => 'Tahun Baru Imlek 2578 Kongzili', 'type' => 'national'],
        '2027-03-09' => ['name' => 'Hari Suci Nyepi (Tahun Baru Saka 1949)', 'type' => 'national'],
        '2027-03-10' => ['name' => 'Hari Raya Idul Fitri 1448 Hijriah', 'type' => 'national'],
        '2027-03-11' => ['name' => 'Hari Raya Idul Fitri 1448 Hijriah (Hari Ke-2)', 'type' => 'national'],
        '2027-03-26' => ['name' => 'Wafat Yesus Kristus (Jumat Agung)', 'type' => 'national'],
        '2027-05-01' => ['name' => 'Hari Buruh Internasional', 'type' => 'national'],
        '2027-05-06' => ['name' => 'Kenaikan Yesus Kristus', 'type' => 'national'],
        '2027-05-20' => ['name' => 'Hari Raya Waisak 2571 BE', 'type' => 'national'],
        '2027-06-01' => ['name' => 'Hari Lahir Pancasila', 'type' => 'national'],
        '2027-06-06' => ['name' => 'Hari Raya Idul Adha 1448 Hijriah', 'type' => 'national'],
        '2027-06-26' => ['name' => 'Tahun Baru Islam 1449 Hijriah', 'type' => 'national'],
        '2027-08-17' => ['name' => 'Hari Kemerdekaan Republik Indonesia ke-82', 'type' => 'national'],
        '2027-09-04' => ['name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],
        '2027-12-25' => ['name' => 'Hari Raya Natal', 'type' => 'national'],
    ];

    /**
     * Check if a given date is a holiday or weekend in Indonesia.
     *
     * @return array{is_holiday: bool, reason: string|null, type: string|null, label: string}
     */
    public function checkDate(string|Carbon $date): array
    {
        $carbonDate = is_string($date) ? Carbon::parse($date) : $date;
        $dateString = $carbonDate->toDateString();

        // 1. Weekend check: Hari Minggu (Strict Holiday / Closed)
        if ($carbonDate->isSunday()) {
            return [
                'is_holiday' => true,
                'reason' => 'Hari Minggu (Layanan Tutup)',
                'type' => 'weekend',
                'label' => 'LIBUR / LAYANAN TUTUP',
            ];
        }

        // 2. Weekend check: Hari Sabtu (Non-operational day)
        if ($carbonDate->isSaturday()) {
            return [
                'is_holiday' => true,
                'reason' => 'Hari Sabtu (Akhir Pekan - Layanan Tutup)',
                'type' => 'weekend',
                'label' => 'LIBUR / LAYANAN TUTUP',
            ];
        }

        // 3. Predefined SKB 3 Menteri Indonesian National Holidays & Cuti Bersama
        if (isset($this->nationalHolidays[$dateString])) {
            $info = $this->nationalHolidays[$dateString];

            return [
                'is_holiday' => true,
                'reason' => $info['name'],
                'type' => $info['type'],
                'label' => 'LIBUR / LAYANAN TUTUP',
            ];
        }

        // 4. Database holiday check (for dynamic admin holidays or local declarations)
        $holiday = Holiday::where('holiday_date', $dateString)->first();
        if ($holiday) {
            return [
                'is_holiday' => true,
                'reason' => $holiday->name,
                'type' => $holiday->type,
                'label' => 'LIBUR / LAYANAN TUTUP',
            ];
        }

        return [
            'is_holiday' => false,
            'reason' => null,
            'type' => null,
            'label' => 'LAYANAN BUKA',
        ];
    }

    /**
     * Get all holidays map for a given month and year.
     * Combines both the built-in Indonesian SKB 3 Menteri dataset and database entries.
     *
     * @return array<string, array{name: string, type: string}>
     */
    public function getHolidaysForMonth(int $year, int $month): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $map = [];

        // 1. Add from built-in national holidays array
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $str = $current->toDateString();
            if (isset($this->nationalHolidays[$str])) {
                $map[$str] = $this->nationalHolidays[$str];
            }
            $current->addDay();
        }

        // 2. Merge with database holidays
        $dbHolidays = Holiday::whereBetween('holiday_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        foreach ($dbHolidays as $h) {
            $map[$h->holiday_date->format('Y-m-d')] = [
                'name' => $h->name,
                'type' => $h->type,
            ];
        }

        return $map;
    }

    /**
     * Check today's operational status.
     *
     * @return array{is_open: bool, status_badge: string, status_text: string, time_range: string}
     */
    public function getTodayOperationalStatus(): array
    {
        $today = Carbon::now();
        $check = $this->checkDate($today);

        if ($check['is_holiday']) {
            return [
                'is_open' => false,
                'status_badge' => 'LIBUR / LAYANAN TUTUP',
                'status_text' => 'Hari ini kantor pelayanan Disdukcapil tutup ('.($check['reason'] ?? 'Hari Libur / Akhir Pekan').'). Pendaftaran antrean online dibuka untuk jadwal hari kerja berikutnya.',
                'time_range' => 'Tutup (Buka kembali hari kerja berikutnya)',
            ];
        }

        $nowTime = $today->format('H:i');
        $isOpenHours = ($nowTime >= '07:30' && $nowTime <= '15:30');

        return [
            'is_open' => true,
            'status_badge' => 'BUKA LAYANAN NORMAL',
            'status_text' => 'Pelayanan loket Disdukcapil Kabupaten Cirebon buka hari ini. Kuota online tersedia untuk 2 sesi shift (Shift Pagi: 08:00 - 12:00 WIB & Shift Siang: 13:00 - 15:00 WIB).',
            'time_range' => '08:00 - 15:00 WIB',
        ];
    }
}
