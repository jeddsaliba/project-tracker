<?php

namespace Database\Seeders;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(15)->create()->each(function ($user) {
            $user->assignRole(Utils::getPanelUserRoleName());
            $date = Carbon::now();
            $user->profile()->create([
                'phone' => fake()->unique()->e164PhoneNumber(),
                'birthdate' => fake()->unique()->dateTimeBetween($date->copy()->subYears(75), $date->copy()->subYears(18))
            ]);
        });
    }
}
