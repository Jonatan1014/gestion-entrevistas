<?php

namespace App\Http\Requests\Test;

use App\Models\Vacancy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttachTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-tests');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'test_id' => ['required', 'exists:tests,id'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Vacancy $vacancy */
            $vacancy = $this->route('vacancy');
            $newWeight = (float) $this->input('weight', 0);
            $testId = (int) $this->input('test_id');

            $currentWeight = $vacancy->tests()
                ->where('test_id', '!=', $testId)
                ->sum('weight');

            if ($currentWeight + $newWeight > 100) {
                $validator->errors()->add(
                    'weight',
                    'The total weight for this vacancy cannot exceed 100%.'
                );
            }
        });
    }
}
