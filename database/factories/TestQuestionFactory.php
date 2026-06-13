<?php

namespace Database\Factories;

use App\Models\Test;
use App\Models\TestQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<TestQuestion>
 */
class TestQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = TestQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_id' => Test::factory(),
            'question_text' => fake()->sentence().'?',
            'options' => ['Option A', 'Option B', 'Option C', 'Option D'],
            'correct_answer_indices' => [0],
            'points' => fake()->randomFloat(2, 5, 20),
            'order' => fake()->unique()->numberBetween(1, 100),
        ];
    }
}
