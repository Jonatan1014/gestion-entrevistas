<?php

namespace Database\Factories;

use App\Enums\TestType;
use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Test>
 */
class TestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Test::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'type' => TestType::NUMERIC,
            'max_score' => fake()->randomFloat(2, 10, 100),
            'evaluation_criteria' => fake()->paragraph(1),
        ];
    }

    /**
     * Indicate that the test is numeric.
     */
    public function numeric(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TestType::NUMERIC,
        ]);
    }

    /**
     * Indicate that the test is text-based.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TestType::TEXT,
        ]);
    }

    /**
     * Indicate that the test is multiple choice.
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TestType::MULTIPLE_CHOICE,
        ]);
    }
}
