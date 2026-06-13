<?php

namespace App\Http\Requests\TestResult;

use App\Enums\TestType;
use App\Models\Test;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordScoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('record-results');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Test|null $test */
        $test = $this->route('test');
        $maxScore = $test?->max_score;

        $rules = [
            'score' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($maxScore) {
                    if ($maxScore !== null && $value > $maxScore) {
                        $fail("The {$attribute} must not exceed {$maxScore}.");
                    }
                },
            ],
            'observations' => ['nullable', 'string', 'max:2000'],
        ];

        if ($test?->type === TestType::MULTIPLE_CHOICE) {
            $rules['answers'] = ['required', 'array'];
        }

        return $rules;
    }
}
