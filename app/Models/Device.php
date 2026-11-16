<?php

namespace App\Models;

use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A terminal: an Android POS device or an NFC phone. Server only.
 *
 * @property string $id
 * @property string $name
 * @property Carbon|null $last_synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['id', 'name', 'last_synced_at'])]
class Device extends Model
{
    /** @use HasFactory<DeviceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<AttendanceEvent, $this>
     */
    public function attendanceEvents(): HasMany
    {
        return $this->hasMany(AttendanceEvent::class);
    }

    /**
     * @return HasMany<RemoteCheckIn, $this>
     */
    public function remoteCheckIns(): HasMany
    {
        return $this->hasMany(RemoteCheckIn::class);
    }
}
