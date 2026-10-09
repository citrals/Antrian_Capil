<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Holiday;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DisdukcapilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Services
        $services = [
            [
                'slug' => 'ktp-el',
                'name' => 'PELAYAN ADMINDUK',
                'subtitle' => 'Perekaman Baru & Penggantian KTP Rusak/Hilang',
                'description' => 'Layanan perekaman biometrik baru (pemula usia 17 tahun), pencetakan ulang fisik KTP-el yang rusak, buram, atau hilang dengan melampirkan surat kepolisian.',
                'icon' => 'id-card',
                'badge' => 'Paling Populer',
                'quota_morning' => 50,
                'quota_afternoon' => 50,
                'quota_evening' => 40,
                'estimated_minutes' => 15,
                'sort_order' => 1,
                'requirements' => [
                    'Fotokopi Kartu Keluarga (KK) terbaru Kabupaten Cirebon',
                    'Surat Keterangan Kehilangan dari Polsek setempat (khusus fisik KTP hilang)',
                    'Fisik KTP lama yang rusak (wajib dibawa saat penukaran)',
                    'Surat Pengantar RT/RW / Formulir Perekaman dari Kantor Camat (bagi pemula)',
                    'Berpakaian rapi, sopan, berkerah (bukan kaos oblong) dan tidak memakai lensa kontak warna',
                ],
            ],
            [
                'slug' => 'kartu-keluarga',
                'name' => 'UPDATE NIK, AKTIVASI IKD, CEK BIOMETRIK',
                'subtitle' => 'Penerbitan Baru, Perubahan Elemen & Pisah KK',
                'description' => 'Penerbitan Kartu Keluarga ber-TTE (Tanda Tangan Elektronik) barcode, penambahan anggota keluarga (kelahiran), pengurangan (kematian/pindah), dan pembaruan biodata.',
                'icon' => 'users',
                'badge' => 'TTE Barcode',
                'quota_morning' => 45,
                'quota_afternoon' => 45,
                'quota_evening' => 35,
                'estimated_minutes' => 20,
                'sort_order' => 2,
                'requirements' => [
                    'Kartu Keluarga (KK) asli yang lama',
                    'Buku Nikah / Akta Perkawinan bagi pasangan suami-istri (asli & fotokopi)',
                    'Surat Keterangan Lahir dari Bidan / Rumah Sakit (jika penambahan anak)',
                    'Ijazah terakhir atau Akta Kelahiran (untuk pembetulan ejaan nama/tgl lahir)',
                    'Surat Keterangan Kematian (jika pengurangan anggota keluarga yang meninggal)',
                ],
            ],
            [
                'slug' => 'akta-kelahiran-kematian',
                'name' => 'KONSULTASI / PENGADUAN',
                'subtitle' => 'Pencatatan Sipil Kutipan Akta Kelahiran & Kematian',
                'description' => 'Layanan penerbitan kutipan resmi Akta Kelahiran untuk bayi hingga dewasa, serta penerbitan Akta Kematian bagi warga yang telah berpulang.',
                'icon' => 'file-text',
                'badge' => 'Gratis & Cepat',
                'quota_morning' => 40,
                'quota_afternoon' => 40,
                'quota_evening' => 30,
                'estimated_minutes' => 15,
                'sort_order' => 3,
                'requirements' => [
                    'Surat Keterangan Kelahiran / Kematian dari Dokter, RS, Bidan, atau Kuwu / Lurah',
                    'Fotokopi KTP-el kedua orang tua atau jenazah',
                    'Fotokopi Kartu Keluarga (KK) pelapor',
                    'Fotokopi KTP-el 2 (dua) orang saksi',
                    'Buku Nikah / Akta Perkawinan orang tua (asli & fotokopi legalisir)',
                ],
            ],
            [
                'slug' => 'pindah-datang',
                'name' => 'LOKET PRIORITAS',
                'subtitle' => 'Pindah Keluar / Kedatangan Masuk Wilayah Cirebon',
                'description' => 'Pelayanan Surat Keterangan Pindah Warga Negara Indonesia (SKPWNI) antar desa, antar kecamatan dalam kabupaten, maupun lintas kabupaten/kota dan provinsi.',
                'icon' => 'map-pin',
                'badge' => 'Satu Hari Jadi',
                'quota_morning' => 35,
                'quota_afternoon' => 35,
                'quota_evening' => 25,
                'estimated_minutes' => 20,
                'sort_order' => 4,
                'requirements' => [
                    'Kartu Keluarga (KK) asli pemohon',
                    'KTP-el asli semua anggota keluarga yang ikut pindah domisili',
                    'Alamat tujuan pindah yang jelas dan lengkap (RT, RW, Desa, Kecamatan, Kab/Kota)',
                    'SKPWNI dari daerah asal (khusus permohonan Kedatangan ke Kab. Cirebon)',
                    'Surat Pernyataan Persetujuan Pemilik Rumah (jika menumpang di KK orang lain)',
                ],
            ],
        ];

        foreach ($services as $data) {
            Service::updateOrCreate(['slug' => $data['slug']], $data);
        }

        // 2. Holidays (Indonesian National Holidays)
        $holidays = [
            // 2026
            ['holiday_date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi', 'type' => 'national'],
            ['holiday_date' => '2026-01-16', 'name' => "Isra Mi'raj Nabi Muhammad SAW", 'type' => 'national'],
            ['holiday_date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577 Kongzili', 'type' => 'national'],
            ['holiday_date' => '2026-03-20', 'name' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'type' => 'national'],
            ['holiday_date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 Hijriah', 'type' => 'national'],
            ['holiday_date' => '2026-03-22', 'name' => 'Hari Raya Idul Fitri 1447 Hijriah (Hari Ke-2)', 'type' => 'national'],
            ['holiday_date' => '2026-03-23', 'name' => 'Cuti Bersama Hari Raya Idul Fitri', 'type' => 'cuti_bersama'],
            ['holiday_date' => '2026-03-24', 'name' => 'Cuti Bersama Hari Raya Idul Fitri', 'type' => 'cuti_bersama'],
            ['holiday_date' => '2026-04-03', 'name' => 'Wafat Yesus Kristus (Jumat Agung)', 'type' => 'national'],
            ['holiday_date' => '2026-05-01', 'name' => 'Hari Buruh Internasional', 'type' => 'national'],
            ['holiday_date' => '2026-05-14', 'name' => 'Kenaikan Yesus Kristus', 'type' => 'national'],
            ['holiday_date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570 BE', 'type' => 'national'],
            ['holiday_date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'type' => 'national'],
            ['holiday_date' => '2026-06-16', 'name' => 'Hari Raya Idul Adha 1447 Hijriah', 'type' => 'national'],
            ['holiday_date' => '2026-07-07', 'name' => 'Tahun Baru Islam 1448 Hijriah', 'type' => 'national'],
            ['holiday_date' => '2026-08-17', 'name' => 'Hari Kemerdekaan Republik Indonesia ke-81', 'type' => 'national'],
            ['holiday_date' => '2026-09-15', 'name' => 'Maulid Nabi Muhammad SAW', 'type' => 'national'],
            ['holiday_date' => '2026-10-01', 'name' => 'Hari Kesaktian Pancasila / Pemeliharaan Sistem', 'type' => 'cuti_bersama'],
            ['holiday_date' => '2026-10-24', 'name' => 'Hari Jadi Disdukcapil (Libur Khusus)', 'type' => 'special'],
            ['holiday_date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national'],
            ['holiday_date' => '2026-12-26', 'name' => 'Cuti Bersama Hari Raya Natal', 'type' => 'cuti_bersama'],
            // 2025
            ['holiday_date' => '2025-01-01', 'name' => 'Tahun Baru 2025 Masehi', 'type' => 'national'],
            ['holiday_date' => '2025-08-17', 'name' => 'Hari Kemerdekaan RI ke-80', 'type' => 'national'],
            ['holiday_date' => '2025-12-25', 'name' => 'Hari Raya Natal', 'type' => 'national'],
        ];

        Holiday::query()->delete();
        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }

        // 3. Sample Appointments to demonstrate real-time remaining quota (e.g. Sisa Kuota: 15 / 50 and Kuota Penuh)
        $ktpService = Service::where('slug', 'ktp-el')->first();
        if ($ktpService) {
            // Find next weekday (e.g. tomorrow or next Monday)
            $targetDate = Carbon::now();
            while ($targetDate->isWeekend() || Holiday::where('holiday_date', $targetDate->toDateString())->exists()) {
                $targetDate->addDay();
            }

            // Create 35 appointments in shift pagi to make remaining quota = 15 / 50 (exactly matching user request example!)
            for ($i = 1; $i <= 35; $i++) {
                $queueNo = sprintf('CAPIL-CBR-A%03d', $i);
                $ticket = sprintf('TKT-%s-%s-%04d', $ktpService->slug, $targetDate->format('Ymd'), $i);
                Appointment::updateOrCreate(
                    ['ticket_number' => $ticket],
                    [
                        'queue_number' => $queueNo,
                        'service_id' => $ktpService->id,
                        'nik' => '3209'.str_pad((string) (100000000000 + $i), 12, '0', STR_PAD_LEFT),
                        'full_name' => 'Warga Simulasi '.$i,
                        'phone' => '081234567'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                        'appointment_date' => $targetDate->toDateString(),
                        'shift' => 'pagi',
                        'status' => 'confirmed',
                    ]
                );
            }

            // Create 50 appointments in shift siang to make it 50 / 50 (KUOTA PENUH / Disabled, matching user request requirement!)
            for ($i = 1; $i <= 50; $i++) {
                $queueNo = sprintf('CAPIL-CBR-B%03d', $i);
                $ticket = sprintf('TKT-%s-SNG-%s-%04d', $ktpService->slug, $targetDate->format('Ymd'), $i);
                Appointment::updateOrCreate(
                    ['ticket_number' => $ticket],
                    [
                        'queue_number' => $queueNo,
                        'service_id' => $ktpService->id,
                        'nik' => '3209'.str_pad((string) (200000000000 + $i), 12, '0', STR_PAD_LEFT),
                        'full_name' => 'Warga Penuh '.$i,
                        'phone' => '081398765'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                        'appointment_date' => $targetDate->toDateString(),
                        'shift' => 'siang',
                        'status' => 'confirmed',
                    ]
                );
            }
        }
    }
}
