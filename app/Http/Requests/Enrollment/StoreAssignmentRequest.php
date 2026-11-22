<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'intern_id' => [
                'required',
                'uuid',
                Rule::exists('interns', 'id')->whereNull('deleted_at'),
            ],
            'site_id' => [
                'required',
                'uuid',
                Rule::exists('sites', 'id')->whereNull('deleted_at'),
                // The same intern cannot be assigned to the same site twice.
                Rule::unique('assignments', 'site_id')->where(
                    fn (Builder $query) => $query
                        ->where('intern_id', $this->input('intern_id'))
                        ->whereNull('deleted_at'),
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'site_id.unique' => 'This intern is already assigned to that site.',
        ];
    }
}
