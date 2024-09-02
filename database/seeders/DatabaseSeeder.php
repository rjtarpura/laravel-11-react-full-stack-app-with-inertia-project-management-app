<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name'              => 'Rakesh Jangir',
            'email'             => 'rakesh@e',
            'email_verified_at' => time(),
            'password'          => bcrypt('Admin@123_4'),
        ]);

        User::factory()->create([
            'name'              => 'Sonu Jangir',
            'email'             => 'sonu@e',
            'email_verified_at' => time(),
            'password'          => bcrypt('Admin@123_4'),
        ]);

        Project::factory()
        ->count(30)
        ->hasTasks(30)
        ->create();
    }
}
