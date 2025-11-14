<?php

namespace Database\Seeders;

use App\Models\TenantEmailConfig;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantEmailConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // WayneEnt - Dark theme with custom branding and CC
        TenantEmailConfig::updateOrCreate(
            ['tenant_slug' => 'wayneent'],
            [
                'logo_url' => null, // TODO: Add wayneent-logo.png to public/images/
                'banner_image_url' => null, // Uses default bookshelf.jpg
                'primary_color' => '#1a1a1a',
                'header_text' => 'Wayne Enterprises',
                'footer_text' => 'Wayne Enterprises - Protecting Gotham Since 1939. Confidential and Proprietary.',
                'support_email' => 'support@wayneent.com',
                'cc_emails' => ['admin@wayneent.com', 'notifications@wayneent.com'],
            ]
        );

        // AcMe - Bright red theme
        TenantEmailConfig::updateOrCreate(
            ['tenant_slug' => 'acme'],
            [
                'logo_url' => asset('images/Acme-corp.webp'),
                'banner_image_url' => null, // Uses default bookshelf.jpg
                'primary_color' => '#dc2626',
                'header_text' => 'AcMe Corporation',
                'footer_text' => 'AcMe Corp - Quality Products Since 1949. For questions, contact your account manager.',
                'support_email' => 'help@acme.com',
                'cc_emails' => [],
            ]
        );

        // Beta - Minimal purple theme (uses default banner)
        TenantEmailConfig::updateOrCreate(
            ['tenant_slug' => 'beta'],
            [
                'logo_url' => null,
                'banner_image_url' => null, // Will use default bookshelf.jpg
                'primary_color' => '#7c3aed',
                'header_text' => 'Beta Company',
                'footer_text' => 'Thank you for being part of our beta program!',
                'support_email' => 'beta@example.com',
                'cc_emails' => [],
            ]
        );
    }
}
