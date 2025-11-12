<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantResolver;
use Illuminate\Http\Request;
use App\Models\User;
use Tests\TestCase;

class TenantResolverTest extends TestCase{
    use RefreshDatabase;
    private Request $request;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $user = User::factory()->create(['organization_id' => 1]);
    }

    public function test_it_resolves_from_the_header_first() {
        $subdomain = "WayneEnt";

        $request = Request::create('https://beta.app.com/test');
        $request->headers->set('X-Tenant-Id', $subdomain);
        $request->setUserResolver(fn() => $this->user);

        $resolver = new TenantResolver($request);
        
        // Header should win despite subdomain and user
        $this->assertEquals(
            strtolower($subdomain), 
            strtolower($resolver->current())
        );
    }

    public function test_it_resolves_from_the_subdomain_second() {
        $request = Request::create('https://beta.app.com/test');
        $request->headers->set('X-Tenant-Id', 'NOT FOUND TENANT');
        $request->setUserResolver(fn() => $this->user);

        $resolver = new TenantResolver($request);
        
        // Subdomain should win despite subdomain and user
        $this->assertEquals('beta', $resolver->current());

    }

    public function test_it_resloves_from_the_authE_user() {
        $request = Request::create('https://beta.app.com/test');
        $request->headers->set('X-Tenant-Id', 'NOT FOUND TENANT');
        $request->setUserResolver(fn() => $this->user);

        $resolver = new TenantResolver($request);
        
        // AuthE user should win despite subdomain and header
        $this->assertEquals('beta', $resolver->current());
    }

    public function test_it_resloves_from_the_default() {

        $user = User::factory()->create(['organization_id' => 2]);

        $request = Request::create('https://notfound.app.com/test');
        $request->headers->set('X-Tenant-Id', 'NOT FOUND TENANT');
        $request->setUserResolver(fn() => $user);

        $resolver = new TenantResolver($request);
        
        // Resolve the default 'tenant'
        $this->assertEquals('AcMe', $resolver->current());

    }
};