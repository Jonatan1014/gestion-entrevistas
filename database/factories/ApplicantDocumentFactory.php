<?php

namespace Database\Factories;

use App\Enums\ApplicantDocumentType;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<ApplicantDocument>
 */
class ApplicantDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = ApplicantDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([ApplicantDocumentType::CV, ApplicantDocumentType::CERTIFICATE]);
        $filename = fake()->uuid.'.pdf';

        return [
            'applicant_id' => Applicant::factory(),
            'type' => $type->value,
            'filename' => $filename,
            'original_name' => fake()->word.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 5120 * 1024),
            'path' => "applicants/{\$this->applicant_id}/{$type->value}/{$filename}",
        ];
    }
}
