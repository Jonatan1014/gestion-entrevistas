<?php

namespace App\Http\Requests\Interview;

use App\Enums\InterviewType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-interviews');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vacancy_id' => ['required', 'exists:vacancies,id'],
            'applicant_id' => ['required', 'exists:applicants,id'],
            'interviewer_id' => ['required', 'exists:users,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(InterviewType::cases(), 'value'))],
            'link' => ['required_if:type,'.InterviewType::VIRTUAL->value, 'url', 'max:500'],
            'location_notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
