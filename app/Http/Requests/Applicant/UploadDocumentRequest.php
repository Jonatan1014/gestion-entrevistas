<?php

namespace App\Http\Requests\Applicant;

use App\Enums\ApplicantDocumentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-applicants');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:5120'],
            'type' => ['required', 'string', 'in:'.implode(',', array_column(ApplicantDocumentType::cases(), 'value'))],
        ];
    }
}
