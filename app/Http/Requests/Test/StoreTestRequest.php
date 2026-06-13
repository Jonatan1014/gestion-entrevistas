<?php

namespace App\Http\Requests\Test;

use App\Enums\TestType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(TestType::cases(), 'value'))],
            'max_score' => ['required', 'numeric', 'min:0'],
            'evaluation_criteria' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
