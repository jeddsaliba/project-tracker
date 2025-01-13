<?php

namespace Database\Seeders;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::factory()->count(100)->create()->each(function ($project, $key) {
            if ($key % 3 == 0) {
                $date = Carbon::now();
                $project->update(['actual_completed_date' => fake()->dateTimeBetween($date->copy()->startOfWeek(), $date->copy()->endOfWeek())]);
                $project->status()->attach(3);
            } else {
                $project->status()->attach(fake()->numberBetween(1, 2));
            }
            $project->users()->attach(fake()->numberBetween(1, 15));
        });
    }
}
