<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'subtitle',
        'description',
        'icon',
        'badge',
        'quota_morning',
        'quota_afternoon',
        'quota_evening',
        'estimated_minutes',
        'requirements',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requirements' => 'array',
        'is_active' => 'boolean',
        'quota_morning' => 'integer',
        'quota_afternoon' => 'integer',
        'quota_evening' => 'integer',
        'estimated_minutes' => 'integer',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get quota for a given shift (pagi, siang).
     */
    public function getQuotaForShift(string $shift): int
    {
        return match ($shift) {
            'pagi' => $this->quota_morning ?? 50,
            'siang' => $this->quota_afternoon ?? 50,
            default => 50,
        };
    }
}
