<?php

namespace Database\Seeders;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::factory()->count(250)->create()->each(function ($task, $key) {
            if ($key % 3 == 0) {
                $date = Carbon::now();
                $task->update(['actual_completed_date' => fake()->dateTimeBetween($date->copy()->startOfWeek(), $date->copy()->endOfWeek())]);
                $task->status()->attach(3);
            } else {
                $task->status()->attach(fake()->numberBetween(1, 2));
            }
        });
    }
}
