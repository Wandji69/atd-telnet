<?php

namespace App\Actions\Enrollment;

use App\Models\Intern;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Enrolls an intern.
 *
 * The PIN is stored only as a hash. The reference photo goes to the local
 * `evidence` disk, and the intern row keeps just the object key.
 */
class CreateIntern
{
    public function handle(string $name, string $pin, UploadedFile $referencePhoto): Intern
    {
        $id = (string) Str::uuid();
        $disk = Storage::disk('evidence');

        $key = $disk->putFileAs(
            'reference',
            $referencePhoto,
            $id.'.'.$referencePhoto->extension(),
        );

        if ($key === false) {
            throw new RuntimeException('The reference photo could not be stored.');
        }

        try {
            return Intern::create([
                'id' => $id,
                'name' => $name,
                'pin_hash' => Hash::make($pin),
                'reference_photo_key' => $key,
            ]);
        } catch (Throwable $e) {
            // Do not leave an orphan file behind when the row was not saved.
            $disk->delete($key);

            throw $e;
        }
    }
}
