<?php

namespace App\Models;

use App\Enums\AttendanceKind;
use App\Enums\CardStatus;
use App\Enums\ReviewStatus;
use Database\Factories\AttendanceEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A terminal or PIN check-in. Pushed by terminals with a client-chosen UUID.
 *
 * @property string $id
 * @property string|null $device_id
 * @property string $intern_id
 * @property AttendanceKind $kind
 * @property CardStatus|null $status
 * @property Carbon $occurred_at
 * @property string|null $photo_key
 * @property ReviewStatus $review_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'id',
    'device_id',
    'intern_id',
    'kind',
    'status',
    'occurred_at',
    'photo_key',
    'review_status',
])]
class AttendanceEvent extends Model
{
    /** @use HasFactory<AttendanceEventFactory> */
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
            'kind' => AttendanceKind::class,
            'status' => CardStatus::class,
            'occurred_at' => 'datetime',
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
