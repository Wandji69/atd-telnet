<?php

namespace App\Actions\Enrollment;

use App\Models\Assignment;

/**
 * Assigns one intern to one site.
 */
class CreateAssignment
{
    public function handle(string $internId, string $siteId): Assignment
    {
        return Assignment::create([
            'intern_id' => $internId,
            'site_id' => $siteId,
        ]);
    }
}
