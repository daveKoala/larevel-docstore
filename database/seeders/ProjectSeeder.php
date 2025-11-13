<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            // AcMe Corporation Projects
            [
                'organization_slug' => 'acme',
                'name' => 'Rocket Skates Development',
                'description' => 'Development of high-speed rocket-powered roller skates',
                'status' => 'active',
            ],
            [
                'organization_slug' => 'acme',
                'name' => 'Giant Magnet Project',
                'description' => 'Industrial-strength magnet research and development',
                'status' => 'active',
            ],
            // Beta Industries Projects
            [
                'organization_slug' => 'beta',
                'name' => 'Beta Testing Platform',
                'description' => 'Internal platform for testing new features',
                'status' => 'active',
            ],
            [
                'organization_slug' => 'beta',
                'name' => 'Cloud Migration',
                'description' => 'Migrate legacy systems to cloud infrastructure',
                'status' => 'active',
            ],
            // Wayne Enterprises Projects
            [
                'organization_slug' => 'wayneent',
                'name' => 'Security Systems Upgrade',
                'description' => 'Next-generation security systems for Wayne Tower',
                'status' => 'active',
            ],
            [
                'organization_slug' => 'wayneent',
                'name' => 'R&D Special Projects',
                'description' => 'Classified research and development initiatives',
                'status' => 'active',
            ],
        ];

        foreach ($projects as $projectData) {
            $organization = Organization::where('slug', $projectData['organization_slug'])->first();

            if ($organization) {
                Project::firstOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'name' => $projectData['name'],
                    ],
                    [
                        'description' => $projectData['description'],
                        'status' => $projectData['status'],
                    ]
                );
            }
        }

        $this->command->info('Projects seeded successfully!');
    }
}
