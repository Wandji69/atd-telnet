<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\RemoteCheckInFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * An off-site check-in with GPS, a cell-tower reference, and a selfie key.
 * Pushed by terminals. The server fills site_id and distance_meters (T9).
 *
 * @property string $id
 * @property string|null $device_id
 * @property string $intern_id
 * @property string|null $site_id
 * @property float $latitude
 * @property float $longitude
 * @property string|null $cell_tower_ref
 * @property Carbon $occurred_at
 * @property string|null $photo_key
 * @property float|null $distance_meters
 * @property ReviewStatus $review_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'id',
    'device_id',
    'intern_id',
    'site_id',
    'latitude',
    'longitude',
    'cell_tower_ref',
    'occurred_at',
    'photo_key',
    'distance_meters',
    'review_status',
])]
class RemoteCheckIn extends Model
{
    /** @use HasFactory<RemoteCheckInFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Matches the column default, so a new model reports it before a refresh.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'review_status' => 'none',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'occurred_at' => 'datetime',
            'distance_meters' => 'float',
            'review_status' => ReviewStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * @return BelongsTo<Intern, $this>
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<Evidence, $this>
     */
    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    /**
     * @return HasMany<ReviewDecision, $this>
     */
    public function reviewDecisions(): HasMany
    {
        return $this->hasMany(ReviewDecision::class);
    }
}
