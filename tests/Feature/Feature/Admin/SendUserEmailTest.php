<?php

namespace Tests\Feature\Feature\Admin;

use App\Models\Organization;
use App\Models\TenantEmailConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendUserEmailTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $betaUser;
    protected User $wayneUser;
    protected Organization $betaOrg;
    protected Organization $wayneOrg;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organizations
        $this->betaOrg = Organization::factory()->create([
            'name' => 'Beta Industries',
            'slug' => 'beta',
        ]);

        $this->wayneOrg = Organization::factory()->create([
            'name' => 'Wayne Enterprises',
            'slug' => 'wayneent',
        ]);

        // Create super admin
        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@test.com',
        ]);

        // Create tenant users
        $this->betaUser = User::factory()->create([
            'email' => 'user@beta.com',
            'name' => 'Beta User',
        ]);
        $this->betaUser->organizations()->attach($this->betaOrg);

        $this->wayneUser = User::factory()->create([
            'email' => 'user@wayne.com',
            'name' => 'Wayne User',
        ]);
        $this->wayneUser->organizations()->attach($this->wayneOrg);

        // Seed tenant email configs
        $this->seed(\Database\Seeders\TenantEmailConfigSeeder::class);
    }

    public function test_super_admin_can_access_email_form(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get("/admin/users/{$this->betaUser->id}/email");

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.email');
        $response->assertViewHas('user', $this->betaUser);
    }

    public function test_non_super_admin_cannot_access_email_form(): void
    {
        $response = $this->actingAs($this->betaUser)
            ->get("/admin/users/{$this->betaUser->id}/email");

        $response->assertStatus(403);
    }

    public function test_can_queue_email_to_user(): void
    {
        Mail::fake();

        $emailData = [
            'subject' => 'Test Email',
            'body' => 'This is a test email message.',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->betaUser->id}/email", $emailData);

        $response->assertRedirect(route('admin.users.email', $this->betaUser));
        $response->assertSessionHas('success');

        Mail::assertQueued(\App\Mail\UserMessage::class, function ($mail) {
            return $mail->user->id === $this->betaUser->id
                && $mail->emailSubject === 'Test Email'
                && $mail->messageBody === 'This is a test email message.';
        });
    }

    public function test_email_uses_recipient_tenant_branding(): void
    {
        Mail::fake();

        $emailData = [
            'subject' => 'Tenant Branding Test',
            'body' => 'Testing tenant-specific branding.',
        ];

        $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->betaUser->id}/email", $emailData);

        Mail::assertQueued(\App\Mail\UserMessage::class, function ($mail) {
            // Beta user should get Beta branding
            return $mail->tenantConfig['header_text'] === 'Beta Company'
                && $mail->tenantConfig['primary_color'] === '#7c3aed';
        });
    }

    public function test_email_includes_tenant_cc_addresses(): void
    {
        Mail::fake();

        $emailData = [
            'subject' => 'CC Test',
            'body' => 'Testing CC functionality.',
        ];

        $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->wayneUser->id}/email", $emailData);

        Mail::assertQueued(\App\Mail\UserMessage::class, function ($mail) {
            // Wayne user emails should have CC addresses
            $ccEmails = $mail->tenantConfig['cc_emails'];
            return in_array('admin@wayneent.com', $ccEmails)
                && in_array('notifications@wayneent.com', $ccEmails);
        });
    }

    public function test_email_validation_requires_subject(): void
    {
        $emailData = [
            'subject' => '',
            'body' => 'Test body',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->betaUser->id}/email", $emailData);

        $response->assertSessionHasErrors('subject');
    }

    public function test_email_validation_requires_body(): void
    {
        $emailData = [
            'subject' => 'Test Subject',
            'body' => '',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->betaUser->id}/email", $emailData);

        $response->assertSessionHasErrors('body');
    }

    public function test_different_tenants_get_different_branding(): void
    {
        Mail::fake();

        // Send to Beta user
        $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->betaUser->id}/email", [
                'subject' => 'Beta Email',
                'body' => 'Email to Beta user',
            ]);

        // Send to Wayne user
        $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->wayneUser->id}/email", [
                'subject' => 'Wayne Email',
                'body' => 'Email to Wayne user',
            ]);

        Mail::assertQueued(\App\Mail\UserMessage::class, 2);

        // Verify Beta branding
        Mail::assertQueued(\App\Mail\UserMessage::class, function ($mail) {
            return $mail->user->id === $this->betaUser->id
                && $mail->tenantConfig['header_text'] === 'Beta Company'
                && $mail->tenantConfig['primary_color'] === '#7c3aed'
                && count($mail->tenantConfig['cc_emails']) === 0;
        });

        // Verify Wayne branding
        Mail::assertQueued(\App\Mail\UserMessage::class, function ($mail) {
            return $mail->user->id === $this->wayneUser->id
                && $mail->tenantConfig['header_text'] === 'Wayne Enterprises'
                && $mail->tenantConfig['primary_color'] === '#1a1a1a'
                && count($mail->tenantConfig['cc_emails']) === 2;
        });
    }

    public function test_email_actually_queues_in_database(): void
    {
        // Set queue connection to database for this test
        config(['queue.default' => 'database']);

        // Don't fake the queue, test real queuing
        $emailData = [
            'subject' => 'Database Queue Test',
            'body' => 'Testing real database queue.',
        ];

        // Clear any existing jobs
        DB::table('jobs')->truncate();

        $this->actingAs($this->superAdmin)
            ->post("/admin/users/{$this->betaUser->id}/email", $emailData);

        // Assert job exists in database
        $this->assertDatabaseHas('jobs', [
            'queue' => 'emails',
        ]);

        // Verify there's exactly 1 job
        $this->assertEquals(1, DB::table('jobs')->count());
    }

    public function test_immediate_send_method_bypasses_queue(): void
    {
        $service = app(\App\Services\EmailService::class);

        // Clear queue
        DB::table('jobs')->truncate();

        // Use the immediate send method
        $result = $service->sendUserMessageNow(
            $this->betaUser,
            'Immediate Test',
            'This should not be queued'
        );

        $this->assertTrue($result);

        // Verify NO jobs were queued
        $this->assertEquals(0, DB::table('jobs')->count());
    }
}
