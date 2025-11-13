<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'AcMe Corporation',
                'slug' => 'acme',
            ],
            [
                'name' => 'Beta Industries',
                'slug' => 'beta',
            ],
            [
                'name' => 'Wayne Enterprises',
                'slug' => 'wayneent',
            ],
        ];

        foreach ($organizations as $org) {
            Organization::firstOrCreate(
                ['slug' => $org['slug']],
                ['name' => $org['name']]
            );
        }

        $this->command->info('Organizations seeded successfully!');
    }
}
