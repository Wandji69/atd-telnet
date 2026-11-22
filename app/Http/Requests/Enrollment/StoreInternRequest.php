<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInternRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'string', 'digits:6'],
            'reference_photo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pin.digits' => 'The PIN must be exactly 6 digits.',
            'reference_photo.mimes' => 'The reference photo must be a JPG or PNG image.',
            'reference_photo.max' => 'The reference photo must not be larger than 2 MB.',
        ];
    }
}
