<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'ticket_number',
        'queue_number',
        'service_id',
        'nik',
        'full_name',
        'phone',
        'address',
        'email',
        'appointment_date',
        'shift',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get masked NIK for privacy protection (UU PDP compliant).
     * Example: 320912******0001
     */
    public function getMaskedNikAttribute(): string
    {
        $nik = $this->nik ?? '';
        if (strlen($nik) >= 16) {
            return substr($nik, 0, 6).'******'.substr($nik, -4);
        }

        return substr($nik, 0, 4).'******'.substr($nik, -2);
    }

    /**
     * Get unique queue number formatted according to standard (e.g. CAPIL-CBR-A012).
     */
    public function getFormattedQueueNumberAttribute(): string
    {
        $queueNo = $this->queue_number ?? '';
        if (str_starts_with($queueNo, 'CAPIL-CBR-')) {
            return $queueNo;
        }

        // Clean up dashes in A-001 -> A001
        $clean = str_replace('-', '', $queueNo);

        return 'CAPIL-CBR-'.$clean;
    }
}
