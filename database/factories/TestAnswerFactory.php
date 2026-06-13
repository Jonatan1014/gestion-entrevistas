<?php

namespace Database\Factories;

use App\Models\TestAnswer;
use App\Models\TestQuestion;
use App\Models\TestResult;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<TestAnswer>
 */
class TestAnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = TestAnswer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_result_id' => TestResult::factory(),
            'test_question_id' => TestQuestion::factory(),
            'selected_indices' => [fake()->numberBetween(0, 3)],
            'is_correct' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the answer is correct.
     */
    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
        ]);
    }

    /**
     * Indicate that the answer is incorrect.
     */
    public function incorrect(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => false,
        ]);
    }
}
