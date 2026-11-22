<?php

namespace App\Actions\Enrollment;

use App\Models\Site;

/**
 * Creates a work site with a circular geofence.
 *
 * When no radius is given, the column is left out so the default of
 * 100 meters applies.
 */
class CreateSite
{
    public function handle(string $name, float $latitude, float $longitude, ?int $radiusMeters = null): Site
    {
        $attributes = [
            'name' => $name,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($radiusMeters !== null) {
            $attributes['radius_meters'] = $radiusMeters;
        }

        return Site::create($attributes);
    }
}
