<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use App\Tenancy\TenantResolver;
use Illuminate\Http\Request;
use Closure;

class ResolveTenantServices
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = app(TenantResolver::class)->current();

        if ($tenant) {
            // Rebind services for this request
            $this->bindTenantServices($tenant);
            
            // Set tenant context for other services
            config(['app.current_tenant' => $tenant]);
            
            // If using separate databases per tenant
            // config(['database.default' => "tenant_{$tenant}"]);
        }
        
        return $next($request);

    }

    protected function bindTenantServices($tenant)
    {
        $namespace = "App\\Customers\\{$tenant}";

        // Check and bind each service
        $bindings = [
            \App\Contracts\HealthServiceInterface::class => "{$namespace}\\HealthService",
            \App\Contracts\NotificationServiceInterface::class => "{$namespace}\\NotificationService",
            \App\Contracts\OrderServiceInterface::class => "{$namespace}\\OrderService",
            // \App\Contracts\PricingService::class => "{$namespace}\\PricingService",
        ];

        foreach ($bindings as $interface => $implementation) {
            if (class_exists($implementation)) {
                app()->bind($interface, $implementation);
            }
        }
    }
}
