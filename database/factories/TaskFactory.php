<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::now();
        $title = fake()->unique()->words(3, true);
        return [
            'project_id' => fake()->numberBetween(1, 50),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->unique()->sentences(2, true),
            'created_by' => fake()->numberBetween(1, 15),
            'expected_completed_date' => fake()->dateTimeBetween($date->copy()->startOfWeek(), $date->copy()->endOfWeek()),
            'created_at' => fake()->dateTimeBetween($date->copy()->startOfWeek(), $date->copy()->endOfWeek()),
            'updated_at' => fake()->dateTimeBetween($date->copy()->startOfWeek(), $date->copy()->endOfWeek())
        ];
    }
}
