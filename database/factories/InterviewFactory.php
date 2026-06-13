<?php

namespace Database\Factories;

use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Models\Applicant;
use App\Models\Interview;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Interview>
 */
class InterviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Interview::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'applicant_id' => Applicant::factory(),
            'interviewer_id' => User::factory(),
            'scheduled_at' => now()->addDay(),
            'type' => InterviewType::VIRTUAL,
            'link' => 'https://meet.example/test',
            'location_notes' => null,
            'status' => InterviewStatus::PENDING,
            'cancellation_reason' => null,
            'observations' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the interview is virtual.
     */
    public function virtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InterviewType::VIRTUAL,
        ]);
    }

    /**
     * Indicate that the interview is presencial.
     */
    public function presencial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InterviewType::PRESENCIAL,
            'link' => null,
        ]);
    }

    /**
     * Indicate that the interview is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InterviewStatus::PENDING,
        ]);
    }

    /**
     * Indicate that the interview is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InterviewStatus::COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the interview is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InterviewStatus::CANCELLED,
        ]);
    }
}
