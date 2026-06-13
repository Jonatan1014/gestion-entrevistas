<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<TestResult>
 */
class TestResultFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = TestResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_id' => Test::factory(),
            'applicant_id' => Applicant::factory(),
            'vacancy_id' => Vacancy::factory(),
            'evaluator_id' => User::factory(),
            'score' => fake()->randomFloat(2, 0, 100),
            'observations' => fake()->optional()->paragraph(),
            'is_manual_override' => false,
            'override_justification' => null,
        ];
    }

    /**
     * Indicate that the result is a manual override.
     */
    public function manualOverride(string $justification): static
    {
        return $this->state(fn (array $attributes) => [
            'is_manual_override' => true,
            'override_justification' => $justification,
        ]);
    }
}
