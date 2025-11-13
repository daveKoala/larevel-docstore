<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Super Admin
            [
                'name' => 'System Administrator',
                'email' => 'admin@system.com',
                'password' => 'password',
                'role' => 'super_admin',
                'organizations' => [],
                'projects' => [],
            ],
            // AcMe Corporation Users
            [
                'name' => 'Wile E. Coyote',
                'email' => 'wile@acme.com',
                'password' => 'password',
                'role' => 'tenant_admin',
                'organizations' => ['acme'],
                'projects' => ['Rocket Skates Development', 'Giant Magnet Project'],
            ],
            [
                'name' => 'Road Runner',
                'email' => 'roadrunner@acme.com',
                'password' => 'password',
                'role' => 'tenant_user',
                'organizations' => ['acme'],
                'projects' => ['Rocket Skates Development'],
            ],
            // Beta Industries Users
            [
                'name' => 'Beta Tester',
                'email' => 'tester@beta.com',
                'password' => 'password',
                'role' => 'tenant_user',
                'organizations' => ['beta'],
                'projects' => ['Beta Testing Platform', 'Cloud Migration'],
            ],
            [
                'name' => 'Beta Admin',
                'email' => 'admin@beta.com',
                'password' => 'password',
                'role' => 'tenant_admin',
                'organizations' => ['beta'],
                'projects' => ['Beta Testing Platform'],
            ],
            // Wayne Enterprises Users
            [
                'name' => 'Bruce Wayne',
                'email' => 'bruce@wayneent.com',
                'password' => 'password',
                'role' => 'tenant_admin',
                'organizations' => ['wayneent'],
                'projects' => ['Security Systems Upgrade', 'R&D Special Projects'],
            ],
            [
                'name' => 'Lucius Fox',
                'email' => 'lucius@wayneent.com',
                'password' => 'password',
                'role' => 'tenant_user',
                'organizations' => ['wayneent'],
                'projects' => ['R&D Special Projects'],
            ],
            // Multi-organization user
            [
                'name' => 'Jane Consultant',
                'email' => 'jane@consultant.com',
                'password' => 'password',
                'role' => 'tenant_user',
                'organizations' => ['acme', 'beta', 'wayneent'],
                'projects' => ['Rocket Skates Development', 'Beta Testing Platform', 'Security Systems Upgrade'],
            ],
        ];

        foreach ($users as $userData) {
            // Create or find user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                ]
            );

            // Attach organizations
            foreach ($userData['organizations'] as $orgSlug) {
                $organization = Organization::where('slug', $orgSlug)->first();
                if ($organization && !$user->organizations->contains($organization->id)) {
                    $user->organizations()->attach($organization->id);
                }
            }

            // Attach projects
            foreach ($userData['projects'] as $projectName) {
                $project = Project::where('name', $projectName)->first();
                if ($project && !$user->projects->contains($project->id)) {
                    $user->projects()->attach($project->id);
                }
            }
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Sample credentials:');
        $this->command->info('  SUPER ADMIN: admin@system.com | Password: password');
        $this->command->info('  Tenant Admin (AcMe): wile@acme.com | Password: password');
        $this->command->info('  Tenant Admin (Wayne): bruce@wayneent.com | Password: password');
        $this->command->info('  Tenant Admin (Beta): admin@beta.com | Password: password');
    }
}
