<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Organization $organization;
    protected Project $project;
    protected Project $otherProject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('X-Tenant-Id', 'Beta');

        // Create organizations
        $this->organization = Organization::create([
            'name' => 'Test Organization',
            'slug' => 'test-org',
        ]);

        $otherOrganization = Organization::create([
            'name' => 'Other Organization',
            'slug' => 'other-org',
        ]);

        // Create projects
        $this->project = Project::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Project',
            'description' => 'A test project',
            'status' => 'active',
        ]);

        $this->otherProject = Project::create([
            'organization_id' => $otherOrganization->id,
            'name' => 'Other Project',
            'description' => 'Another test project',
            'status' => 'active',
        ]);

        // Create users
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Other User',
            'email' => 'other@example.com',
        ]);

        // Assign user to organization and project
        $this->user->organizations()->attach($this->organization->id);
        $this->user->projects()->attach($this->project->id);

        // Assign other user to other project only
        $this->otherUser->organizations()->attach($otherOrganization->id);
        $this->otherUser->projects()->attach($this->otherProject->id);
    }

    public function test_user_can_create_order_for_their_project(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'project_guid' => $this->project->guid,
            'details' => 'Test order details',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'guid',
                    'user_id',
                    'project_id',
                    'details',
                    'created_at',
                    'updated_at',
                    'user',
                    'project',
                ],
                'message',
            ])
            ->assertJson([
                'message' => 'Order created successfully',
                'data' => [
                    'details' => 'Test order details',
                    'user_id' => $this->user->id,
                    'project_id' => $this->project->id,
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'details' => 'Test order details',
        ]);
    }

    public function test_user_cannot_create_order_for_project_they_dont_belong_to(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'project_guid' => $this->otherProject->guid,
            'details' => 'Test order details',
        ]);

        $response->assertStatus(403);
    }

    public function test_create_order_requires_authentication(): void
    {
        $response = $this->postJson('/api/orders', [
            'project_guid' => $this->project->guid,
            'details' => 'Test order details',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_order_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_guid', 'details']);
    }

    public function test_create_order_validates_details_max_length(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'project_guid' => $this->project->guid,
            'details' => str_repeat('a', 5001), // Exceeds 5000 character limit
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['details']);
    }

    public function test_create_order_validates_project_exists(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'project_guid' => 'nonexistent-guid',
            'details' => 'Test order details',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_guid']);
    }

    public function test_user_can_list_their_accessible_orders(): void
    {
        Sanctum::actingAs($this->user);

        // Create orders for user's project
        Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
        ]);

        // Create order for other project (should not be visible)
        Order::factory()->create([
            'user_id' => $this->otherUser->id,
            'project_id' => $this->otherProject->id,
        ]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'guid',
                        'user_id',
                        'project_id',
                        'details',
                        'created_at',
                        'updated_at',
                        'user',
                        'project',
                    ],
                ],
                'message',
                'continuationToken',
                'nextURL',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_list_orders_requires_authentication(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }

    public function test_list_orders_is_paginated(): void
    {
        Sanctum::actingAs($this->user);

        // Create more orders than pagination limit
        Order::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data'); // Default pagination is 10

        // Check if continuation token exists for next page
        $data = $response->json();
        $this->assertNotEmpty($data['continuationToken']);
    }

    public function test_user_can_view_single_order_by_guid(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/orders/{$order->guid}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'guid',
                    'user_id',
                    'project_id',
                    'details',
                    'created_at',
                    'updated_at',
                    'user',
                    'project',
                ],
                'message',
            ])
            ->assertJson([
                'message' => 'Order retrieved successfully',
                'data' => [
                    'guid' => $order->guid,
                    'details' => $order->details,
                ],
            ]);
    }

    public function test_user_cannot_view_order_from_project_they_dont_belong_to(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->otherUser->id,
            'project_id' => $this->otherProject->id,
        ]);

        $response = $this->getJson("/api/orders/{$order->guid}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have access to this order',
            ]);
    }

    public function test_view_single_order_requires_authentication(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/orders/{$order->guid}");

        $response->assertStatus(401);
    }

    public function test_view_nonexistent_order_returns_404(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders/nonexistent-guid');

        $response->assertStatus(404);
    }

    public function test_update_order_returns_not_implemented(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->putJson("/api/orders/{$order->guid}", [
            'details' => 'Updated details',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Update functionality not yet implemented',
            ]);
    }

    public function test_delete_order_returns_not_implemented(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->deleteJson("/api/orders/{$order->guid}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Delete functionality not yet implemented',
            ]);
    }

    public function test_orders_have_snowflake_guids(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'project_guid' => $this->project->guid,
            'details' => 'Test order details',
        ]);

        $response->assertStatus(201);

        $order = Order::latest()->first();

        // Snowflake IDs are numeric strings
        $this->assertNotEmpty($order->guid);
        $this->assertIsString($order->guid);
        $this->assertMatchesRegularExpression('/^\d+$/', $order->guid);
    }
}
