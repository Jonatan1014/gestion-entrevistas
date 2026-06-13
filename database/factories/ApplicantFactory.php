<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Applicant>
 */
class ApplicantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Applicant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_by' => null,
            'blocked_at' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the applicant is blocked.
     */
    public function blocked(?User $blockedBy = null, string $reason = 'Blocked for testing'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_blocked' => true,
            'block_reason' => $reason,
            'blocked_by' => $blockedBy?->id ?? User::factory(),
            'blocked_at' => now(),
        ]);
    }
}
