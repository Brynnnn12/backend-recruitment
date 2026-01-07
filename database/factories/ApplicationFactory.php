<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'vacancy_id' => \App\Models\Vacancy::factory(),
            'cv_file' => $this->faker->filePath(),
            'status' => $this->faker->randomElement([
                'applied',
                'reviewed',
                'interview',
                'hired',
                'rejected'
            ]),
            'applied_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
