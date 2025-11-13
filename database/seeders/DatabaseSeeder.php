<?php

namespace Database\Seeders;

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
        // Seed in order: Organizations -> Projects -> Users (with relationships)
        $this->call([
            OrganizationSeeder::class,
            ProjectSeeder::class,
            UserSeeder::class,
        ]);
    }
}
