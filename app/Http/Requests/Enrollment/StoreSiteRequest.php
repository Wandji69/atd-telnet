<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            // Optional. An empty field arrives as null and the default of
            // 100 meters is used.
            'radius_meters' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
