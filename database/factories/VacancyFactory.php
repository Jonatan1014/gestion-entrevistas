<?php

namespace Database\Factories;

use App\Enums\VacancyStatus;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacancy>
 */
class VacancyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Vacancy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => fake()->jobTitle(),
            'location' => fake()->city(),
            'requirements' => fake()->paragraph(3),
            'status' => VacancyStatus::OPEN,
            'min_grade' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the vacancy is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VacancyStatus::OPEN,
        ]);
    }

    /**
     * Indicate that the vacancy is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VacancyStatus::CLOSED,
        ]);
    }

    /**
     * Indicate that the vacancy is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VacancyStatus::CANCELLED,
        ]);
    }
}
