<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * UUID primary keys that a client (the phone) may choose.
 *
 * A supplied id is stored unchanged. When no id is supplied, a new UUID is
 * generated. An id that is not a canonical UUID (8-4-4-4-12 hex) is rejected
 * before it reaches the database, on every driver.
 */
trait HasClientUuids
{
    use HasUuids;

    protected static function bootHasClientUuids(): void
    {
        static::saving(function ($model): void {
            $key = $model->getKey();

            if ($key !== null && ! Str::isUuid($key)) {
                throw new InvalidArgumentException(sprintf(
                    'The %s primary key must be a canonical UUID.',
                    class_basename($model),
                ));
            }
        });
    }
}
