<?php

namespace App\Tenancy;

use App\Exceptions\NoTenantException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;




class TenantResolver
{

    private ?string $tenant = null;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function current()
    {
        if ($this->tenant !== null) {
            return $this->tenant;
        }

        // Priority order matters here - most specific to least specific
        $this->tenant = $this->fromHeader()
            ?? $this->fromSubdomain()
            ?? $this->fromAuthenticatedUser()
            // ?? $this->fromApiKey()
            ?? $this->fromDefaultConfig();

        return $this->tenant;
    }

    private function fromHeader(): ?string
    {

        $tenantHeader = $this->request->header('X-Tenant-Id');

        if ($tenantHeader && $this->isValidTenant($tenantHeader)) {
            return $tenantHeader;
        }


        return null;
    }

    private function fromSubdomain()
    {
        // acme.myapp.com → 'acme'
        $host = $this->request->getHost();
        $parts = explode('.', $host);

        if (count($parts) >= 3) {  // Ensure we have a subdomain
            $subdomain = $parts[0];

            // Map subdomain to internal tenant ID if needed
            $tenantId = $this->subdomainToTenantId($subdomain);

            if ($this->isValidTenant($tenantId)) {
                return $tenantId;
            }
        }

        return null;
    }

    private function fromAuthenticatedUser()
    {
        // Get authenticated user's first organization
        $user = $this->request->user();

        if ($user && $user->organizations()->exists()) {
            $firstOrg = $user->organizations()->first();

            if ($this->isValidTenant($firstOrg->slug)) {
                return $firstOrg->slug;
            }
        }

        return null;
    }

    private function fromApiKey()
    {
        return null;
    }

    private function fromDefaultConfig()
    {
        return config('app.default_tenant');
    }

    private function isValidTenant(string $str): bool
    {
        $tenants = [
            'AcMe',
            'Beta',
            'WayneEnt'
        ];

        return in_array(
            strtolower($str),
            array_map('strtolower', $tenants)
        );
    }

    private function subdomainToTenantId(string $str): string
    {
        return $str;
    }


    // For testing and queue jobs where there's no HTTP context
    public function setTenant(string $tenant): void
    {
        $this->tenant = $tenant;
    }

    // For middleware to ensure tenant is set
    public function required(): string
    {
        $tenant = $this->current();
        
        if (!$tenant) {
            throw new NoTenantException('Unable to determine tenant context');
        }
        
        return $tenant;
    }
}
