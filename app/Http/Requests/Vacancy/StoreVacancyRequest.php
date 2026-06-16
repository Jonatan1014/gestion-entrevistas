<?php

namespace App\Http\Requests\Vacancy;

use App\Enums\VacancyStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreVacancyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-vacancies');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'position' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'requirements' => ['required', 'string'],
            'status' => ['sometimes', 'string', 'in:' . implode(',', array_column(VacancyStatus::cases(), 'value'))],
            'min_grade' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'test_ids' => ['nullable', 'array'],
            'test_ids.*' => ['exists:tests,id'],
        ];
    }
}