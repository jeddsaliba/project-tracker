<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checklist>
 */
class ChecklistFactory extends Factory
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
            'task_id' => fake()->numberBetween(1, 1000),
            'title' => $title,
            'description' => fake()->unique()->sentences(2, true),
            'is_done' => fake()->boolean(50),
            'created_by' => fake()->numberBetween(1, 15),
            'created_at' => fake()->dateTimeBetween($date->copy()->startOfWeek(), $date->copy()->endOfWeek()),
            'updated_at' => fake()->dateTimeBetween($date->copy()->startOfWeek(), $date->copy()->endOfWeek())
        ];
    }
}
