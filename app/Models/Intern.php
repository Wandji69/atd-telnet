<?php

namespace App\Models;

use Database\Factories\InternFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * An intern profile. Pulled by terminals.
 *
 * @property string $id
 * @property string $name
 * @property string|null $pin_hash
 * @property string|null $reference_photo_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['id', 'name', 'pin_hash', 'reference_photo_key'])]
#[Hidden(['pin_hash'])]
class Intern extends Model
{
    /** @use HasFactory<InternFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return HasMany<Assignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * @return HasMany<Card, $this>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
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

    /**
     * @return HasMany<Evidence, $this>
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }
}
