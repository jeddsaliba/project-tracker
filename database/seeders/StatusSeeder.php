<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = [
            [
                'name' => 'Pending',
                'color' => '#ff4444'
            ],
            [
                'name' => 'Ongoing',
                'color' => '#ffbb33'
            ],
            [
                'name' => 'Completed',
                'color' => '#00C851'
            ]
        ];
        if (Status::all()->isEmpty()) {
            collect($status)->each(function ($status) {
                $status['slug'] = Str::slug($status['name']);
                Status::create($status);
            });
        }
    }
}
