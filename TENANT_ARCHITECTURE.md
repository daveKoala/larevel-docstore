# Tenant-Based Service Customization Architecture

> **Note**: This documentation reflects the current implementation where `TenantResolver` is located in `app/Tenancy/` for proper separation of concerns. See [Directory Structure Decision](#directory-structure-decision) for details.

## Table of Contents

1. [Why This Approach?](#why-this-approach)
2. [Architecture Overview](#architecture-overview)
3. [Directory Structure Decision](#directory-structure-decision)
4. [Components Breakdown](#components-breakdown)
5. [File Structure](#file-structure)
6. [Required Registrations](#required-registrations)
7. [Adding a New Tenant](#adding-a-new-tenant)
8. [Adding a New Tenant-Customizable Service](#adding-a-new-tenant-customizable-service)
9. [Testing](#testing)
10. [Configuration Options](#configuration-options)
11. [Best Practices](#best-practices)
12. [Common Pitfalls](#common-pitfalls)
13. [Performance Considerations](#performance-considerations)
14. [Extending the Pattern](#extending-the-pattern)
15. [Summary](#summary)
16. [Implementation Checklist](#implementation-checklist)

---

## Why This Approach?

In multi-tenant applications, different customers often require **slightly different implementations** of the same functionality. Rather than cluttering your core business logic with conditional statements, this architecture allows you to:

- **Isolate customer-specific code** in dedicated namespaces
- **Override services per tenant** without touching core logic
- **Maintain clean separation** between default and custom implementations
- **Scale easily** as you add more tenants and customizations
- **Test independently** for each tenant's unique behavior

### Real-World Example

Imagine you have three customers:
- **AcMe Corp**: Uses standard health checks
- **Beta Inc**: Uses standard health checks
- **WayneEnt**: Requires custom health monitoring with bat signal status

Without this pattern, you'd end up with:
```php
public function getStatus() {
    $status = [...];

    if ($tenant === 'WayneEnt') {
        $status['bat_signal'] = 'ready'; // ❌ Messy!
    }

    return $status;
}
```

With this pattern:
```php
// Default implementation stays clean
// WayneEnt gets its own HealthService class
// No conditionals needed! ✅
```

---

## Architecture Overview

### Core Principle

**Interface-based dependency injection + Runtime service rebinding**

1. Define interfaces for services that may vary by tenant
2. Bind default implementations at boot time
3. Detect tenant on each request
4. Rebind tenant-specific implementations if they exist
5. Controllers receive the appropriate implementation automatically

### Request Flow

```
┌─────────────────┐
│  HTTP Request   │
│ X-Tenant-Id:    │
│   WayneEnt      │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────┐
│ ResolveTenantServices       │
│ Middleware                  │
│                             │
│ 1. Resolve tenant           │
│ 2. Check for custom impl    │
│ 3. Rebind if exists         │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│ Service Container           │
│                             │
│ HealthServiceInterface      │
│         ↓                   │
│ WayneEnt\HealthService ✅   │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│ HealthController            │
│                             │
│ Receives WayneEnt version   │
│ automatically               │
└─────────────────────────────┘
```

---

## Directory Structure Decision

### Why `app/Tenancy/` for TenantResolver?

The `TenantResolver` is placed in `app/Tenancy/` rather than `app/Services/` or `app/Http/` because:

1. **Not Business Logic**: TenantResolver is infrastructure/framework code, not business domain logic
2. **Cross-Cutting Concern**: Used in HTTP requests, console commands, queue jobs, and tests
3. **Semantic Clarity**: `App\Tenancy\TenantResolver` clearly indicates its purpose
4. **Scalability**: Room to add related classes (`TenantContext`, `TenantManager`, etc.)
5. **Not HTTP-Specific**: While often used via middleware, it's also used outside HTTP contexts (queues, console, tests)

This keeps tenant infrastructure grouped together and separate from:
- `app/Services/`: Business domain services (HealthService, PricingService, etc.)
- `app/Http/`: HTTP-only components (Controllers, Middleware, Requests, Resources)
- `app/Customers/`: Tenant-specific implementations

---

## Components Breakdown

### 1. TenantResolver (`app/Tenancy/TenantResolver.php`)

**Purpose**: Determines which tenant the current request belongs to

**Methods**:
- `current()`: Returns the tenant identifier (or null)
- `required()`: Returns tenant or throws exception
- `setTenant()`: Manually set tenant (for testing/queues)

**Resolution Priority**:
1. **Header** (`X-Tenant-Id`) - Highest priority
2. **Subdomain** (`acme.myapp.com`)
3. **Authenticated User** - User's tenant association
4. **API Key** - Tenant from API key
5. **Default Config** - Fallback from config

**Validation**:
- Checks against a whitelist of valid tenants
- Case-insensitive matching

### 2. ResolveTenantServices Middleware (`app/Http/Middleware/ResolveTenantServices.php`)

**Purpose**: Intercepts requests and rebinds tenant-specific services

**Process**:
1. Resolves the tenant using TenantResolver
2. Builds namespace: `App\Customers\{TenantId}`
3. Checks if tenant-specific implementations exist
4. Rebinds interfaces to tenant implementations
5. Sets context in config for reference

**Binding Configuration**:
```php
$bindings = [
    \App\Contracts\HealthServiceInterface::class => "{$namespace}\\HealthService",
    \App\Contracts\PricingService::class => "{$namespace}\\PricingService",
];
```

### 3. Service Interface (`app/Contracts/HealthServiceInterface.php`)

**Purpose**: Defines the contract that all implementations must follow

```php
interface HealthServiceInterface
{
    public function getStatus(): array;
}
```

### 4. Default Implementation (`app/Services/HealthService.php`)

**Purpose**: Standard implementation used when no tenant-specific version exists

**Features**:
- Database connectivity check
- Cache connectivity check
- Application status
- Server information

### 5. Tenant-Specific Implementation (`app/Customers/WayneEnt/HealthService.php`)

**Purpose**: Custom implementation for WayneEnt tenant

**Customizations**:
- Adds tenant identifier to response
- Custom cache key prefix
- Additional "bat signal" status
- Special service monitoring

### 6. Controller (`app/Http/Controllers/HealthController.php`)

**Purpose**: Uses the health service via dependency injection

**Key Point**: The controller doesn't know or care which implementation it receives. Laravel's service container injects the appropriate one based on middleware bindings.

### 7. Service Provider (`app/Providers/AppServiceProvider.php`)

**Purpose**: Registers default service bindings at application boot

```php
$this->app->bind(HealthServiceInterface::class, HealthService::class);
```

---

## File Structure

```
app/
├── Contracts/
│   └── HealthServiceInterface.php      # Service contract
├── Tenancy/
│   └── TenantResolver.php              # Tenant detection logic
├── Services/
│   └── HealthService.php               # Default implementation
├── Customers/
│   ├── WayneEnt/
│   │   └── HealthService.php           # WayneEnt custom impl
│   ├── AcMe/
│   │   └── PricingService.php          # AcMe-only service
│   └── Beta/
│       └── HealthService.php           # Beta custom impl
├── Http/
│   ├── Controllers/
│   │   └── HealthController.php        # Uses interface
│   └── Middleware/
│       └── ResolveTenantServices.php   # Rebinding logic
├── Exceptions/
│   └── NoTenantException.php           # Tenant-related errors
└── Providers/
    └── AppServiceProvider.php          # Default bindings

routes/
└── api.php                              # Route with middleware
```

---

## Required Registrations

### 1. Default Service Binding

**File**: `app/Providers/AppServiceProvider.php`

```php
public function register(): void
{
    // Register default implementation
    $this->app->bind(HealthServiceInterface::class, HealthService::class);
}
```

**When**: Application boot (before any requests)

### 2. Middleware Registration

**File**: `bootstrap/app.php` (Laravel 11) or `app/Http/Kernel.php` (Laravel 10)

```php
// Option 1: Apply to specific routes
Route::get('/status', [HealthController::class, 'status'])
    ->middleware(\App\Http\Middleware\ResolveTenantServices::class);

// Option 2: Create middleware alias
protected $middlewareAliases = [
    'tenant' => \App\Http\Middleware\ResolveTenantServices::class,
];

// Then use: ->middleware('tenant')
```

### 3. Tenant-Specific Binding Configuration

**File**: `app/Http/Middleware/ResolveTenantServices.php`

```php
protected function bindTenantServices($tenant)
{
    $namespace = "App\\Customers\\{$tenant}";

    $bindings = [
        \App\Contracts\HealthServiceInterface::class => "{$namespace}\\HealthService",
        // Add more service interfaces here
    ];

    foreach ($bindings as $interface => $implementation) {
        if (class_exists($implementation)) {
            app()->bind($interface, $implementation);
        }
    }
}
```

---

## Adding a New Tenant

### Step 1: Add to Valid Tenants List

**File**: `app/Tenancy/TenantResolver.php`

```php
private function isValidTenant(string $str): bool
{
    $tenants = [
        'AcMe',
        'Beta',
        'WayneEnt',
        'NewTenant', // ✅ Add here
    ];

    return in_array(
        strtolower($str),
        array_map('strtolower', $tenants)
    );
}
```

### Step 2: Create Tenant Directory

```bash
mkdir -p app/Customers/NewTenant
```

### Step 3: Create Custom Implementation (if needed)

**File**: `app/Customers/NewTenant/HealthService.php`

```php
<?php

namespace App\Customers\NewTenant;

use App\Contracts\HealthServiceInterface;

class HealthService implements HealthServiceInterface
{
    public function getStatus(): array
    {
        // Custom implementation for NewTenant
        return [
            'tenant' => 'NewTenant',
            'custom_field' => 'special_value',
            // ... rest of status
        ];
    }
}
```

### Step 4: Test

```bash
# Test with header
curl -H "X-Tenant-Id: NewTenant" http://localhost:8000/api/status

# Or with subdomain (if configured)
curl http://newtenant.myapp.test/api/status
```

---

## Adding a New Tenant-Customizable Service

### Step 1: Create Interface

**File**: `app/Contracts/PricingServiceInterface.php`

```php
<?php

namespace App\Contracts;

interface PricingServiceInterface
{
    public function calculatePrice(array $items): float;
}
```

### Step 2: Create Default Implementation

**File**: `app/Services/PricingService.php`

```php
<?php

namespace App\Services;

use App\Contracts\PricingServiceInterface;

class PricingService implements PricingServiceInterface
{
    public function calculatePrice(array $items): float
    {
        // Standard pricing logic
        return array_sum(array_column($items, 'price'));
    }
}
```

### Step 3: Register Default Binding

**File**: `app/Providers/AppServiceProvider.php`

```php
public function register(): void
{
    $this->app->bind(HealthServiceInterface::class, HealthService::class);
    $this->app->bind(PricingServiceInterface::class, PricingService::class); // ✅ Add
}
```

### Step 4: Add to Middleware Bindings

**File**: `app/Http/Middleware/ResolveTenantServices.php`

```php
$bindings = [
    \App\Contracts\HealthServiceInterface::class => "{$namespace}\\HealthService",
    \App\Contracts\PricingServiceInterface::class => "{$namespace}\\PricingService", // ✅ Add
];
```

### Step 5: Create Tenant-Specific Implementation (Optional)

**File**: `app/Customers/WayneEnt/PricingService.php`

```php
<?php

namespace App\Customers\WayneEnt;

use App\Contracts\PricingServiceInterface;

class PricingService implements PricingServiceInterface
{
    public function calculatePrice(array $items): float
    {
        // WayneEnt gets 10% discount
        $total = array_sum(array_column($items, 'price'));
        return $total * 0.9;
    }
}
```

### Step 6: Use in Controller

```php
<?php

namespace App\Http\Controllers;

use App\Contracts\PricingServiceInterface;

class CheckoutController extends Controller
{
    public function __construct(
        private PricingServiceInterface $pricingService
    ) {}

    public function calculate(Request $request)
    {
        $items = $request->input('items');
        $total = $this->pricingService->calculatePrice($items);

        return response()->json(['total' => $total]);
    }
}
```

---

## Testing

### Testing with Different Tenants

#### 1. Using HTTP Headers

```bash
# Default tenant (or no tenant)
curl http://localhost:8000/api/status

# AcMe tenant
curl -H "X-Tenant-Id: AcMe" http://localhost:8000/api/status

# WayneEnt tenant
curl -H "X-Tenant-Id: WayneEnt" http://localhost:8000/api/status
```

#### 2. Using Subdomains (requires configuration)

```bash
curl http://acme.myapp.test/api/status
curl http://wayneent.myapp.test/api/status
```

#### 3. In PHPUnit Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Tenancy\TenantResolver;

class TenantHealthCheckTest extends TestCase
{
    public function test_default_health_service()
    {
        $response = $this->getJson('/api/status');

        $response->assertStatus(200)
                 ->assertJsonMissing(['tenant']);
    }

    public function test_wayneent_custom_health_service()
    {
        $response = $this->withHeader('X-Tenant-Id', 'WayneEnt')
                         ->getJson('/api/status');

        $response->assertStatus(200)
                 ->assertJson([
                     'tenant' => 'WayneEnt',
                     'wayne_custom' => [
                         'bat_signal' => 'ready'
                     ]
                 ]);
    }

    public function test_manual_tenant_setting()
    {
        $resolver = app(TenantResolver::class);
        $resolver->setTenant('WayneEnt');

        $this->assertEquals('WayneEnt', $resolver->current());
    }
}
```

#### 4. Testing Queue Jobs

```php
use App\Tenancy\TenantResolver;

class ProcessOrderJob
{
    public function handle()
    {
        // Manually set tenant context
        $resolver = app(TenantResolver::class);
        $resolver->setTenant($this->tenantId);

        // Now tenant-specific services will be used
    }
}
```

---

## Configuration Options

### Tenant List Configuration

**Option 1: Hardcoded (Current)**
```php
// In TenantResolver.php
private function isValidTenant(string $str): bool
{
    $tenants = ['AcMe', 'Beta', 'WayneEnt'];
    // ...
}
```

**Option 2: Config File (Recommended)**

Create `config/tenants.php`:
```php
<?php

return [
    'tenants' => [
        'AcMe',
        'Beta',
        'WayneEnt',
    ],

    'subdomain_mapping' => [
        'acme' => 'AcMe',
        'beta' => 'Beta',
        'wayne' => 'WayneEnt',
    ],
];
```

Update `app/Tenancy/TenantResolver.php`:
```php
private function isValidTenant(string $str): bool
{
    $tenants = config('tenants.tenants', []);
    return in_array(
        strtolower($str),
        array_map('strtolower', $tenants)
    );
}
```

### Default Tenant

**File**: `config/app.php`

```php
return [
    // ... other config

    'default_tenant' => env('DEFAULT_TENANT', 'AcMe'),
];
```

**File**: `.env`

```env
DEFAULT_TENANT=AcMe
```

---

## Best Practices

### 1. Keep Interfaces Minimal

```php
// ✅ Good - focused interface
interface PricingServiceInterface
{
    public function calculatePrice(array $items): float;
}

// ❌ Bad - too many responsibilities
interface PricingServiceInterface
{
    public function calculatePrice(array $items): float;
    public function applyDiscount(float $price): float;
    public function validateCoupon(string $code): bool;
    public function getShippingCost(): float;
}
```

### 2. Use Type Hints Everywhere

```php
// ✅ Good
public function current(): ?string

// ❌ Bad
public function current()
```

### 3. Document Tenant-Specific Behavior

```php
/**
 * WayneEnt Custom Health Service
 *
 * Differences from default:
 * - Adds bat_signal status check
 * - Includes special_service monitoring
 * - Uses 'wayne_' prefix for cache keys
 */
class HealthService implements HealthServiceInterface
```

### 4. Test Both Default and Custom Implementations

Always test:
- Default behavior (no tenant)
- Each tenant's custom behavior
- Fallback when tenant implementation doesn't exist

### 5. Don't Duplicate Too Much

If multiple tenants need similar customizations, consider:
- Abstract base classes
- Traits for shared behavior
- Configuration-driven differences

```php
// Good approach for small differences
class WayneEntHealthService extends HealthService
{
    protected function getAdditionalChecks(): array
    {
        return ['bat_signal' => 'ready'];
    }
}
```

### 6. Log Tenant Resolution

```php
public function handle(Request $request, Closure $next): Response
{
    $tenant = app(TenantResolver::class)->current();

    if ($tenant) {
        Log::debug("Resolved tenant: {$tenant}");
        $this->bindTenantServices($tenant);
    }

    return $next($request);
}
```

---

## Common Pitfalls

### 1. Forgetting to Add Middleware

```php
// ❌ Won't use tenant-specific services
Route::get('/status', [HealthController::class, 'status']);

// ✅ Correct
Route::get('/status', [HealthController::class, 'status'])
    ->middleware(\App\Http\Middleware\ResolveTenantServices::class);
```

### 2. Not Registering Default Binding

If you forget the default binding, you'll get:
```
Target [App\Contracts\HealthServiceInterface] is not instantiable.
```

Always register in `AppServiceProvider::register()`.

### 3. Wrong Namespace

```php
// ❌ Wrong
namespace App\Customer\WayneEnt;

// ✅ Correct
namespace App\Customers\WayneEnt;
```

Must match the namespace pattern in middleware: `App\Customers\{$tenant}`

### 4. Not Implementing Interface

```php
// ❌ Will cause errors
class HealthService // Missing "implements HealthServiceInterface"

// ✅ Correct
class HealthService implements HealthServiceInterface
```

---

## Performance Considerations

### 1. Resolution Caching

TenantResolver caches the result in memory:
```php
if ($this->tenant !== null) {
    return $this->tenant; // ✅ Cached
}
```

### 2. Class Existence Check

Middleware checks if tenant implementation exists before binding:
```php
if (class_exists($implementation)) { // ✅ Only binds if exists
    app()->bind($interface, $implementation);
}
```

### 3. Singleton vs Bind

```php
// Creates new instance each time (current approach)
$this->app->bind(Interface::class, Implementation::class);

// Creates once per request (use if expensive to instantiate)
$this->app->singleton(Interface::class, Implementation::class);
```

---

## Extending the Pattern

### Database Per Tenant

```php
// In ResolveTenantServices middleware
if ($tenant) {
    config(['database.default' => "tenant_{$tenant}"]);
}
```

Then configure in `config/database.php`:
```php
'connections' => [
    'tenant_WayneEnt' => [
        'driver' => 'mysql',
        'database' => 'wayne_db',
        // ...
    ],
],
```

### View Customization

```php
// In middleware
if ($tenant) {
    View::addNamespace('tenant', resource_path("views/tenants/{$tenant}"));
}
```

### Config Overrides

```php
// In middleware
if ($tenant) {
    $overrides = config("tenants.{$tenant}.overrides", []);
    foreach ($overrides as $key => $value) {
        config([$key => $value]);
    }
}
```

---

## Summary

This tenant architecture provides:

✅ **Clean code separation** - No conditionals in business logic
✅ **Easy extensibility** - Add tenants without touching existing code
✅ **Type safety** - Interface-based design with dependency injection
✅ **Testability** - Each tenant can be tested independently
✅ **Maintainability** - Changes to one tenant don't affect others
✅ **Scalability** - Pattern works for dozens of tenants

The key insight: **Let Laravel's service container do the heavy lifting**. Define interfaces, create implementations, and let middleware rebind at runtime. Your controllers stay clean and unaware of tenant-specific logic.

---

## Implementation Checklist

When implementing this pattern in a new Laravel project:

- [ ] Create `app/Contracts/` directory for service interfaces
- [ ] Create `app/Tenancy/` directory for tenant infrastructure
- [ ] Create `app/Customers/` directory for tenant-specific code
- [ ] Implement `TenantResolver` with resolution strategy (header, subdomain, etc.)
- [ ] Create `ResolveTenantServices` middleware
- [ ] Register default service bindings in `AppServiceProvider`
- [ ] Update middleware bindings configuration
- [ ] Add tenant validation logic
- [ ] Write tests for tenant resolution
- [ ] Document tenant-specific implementations
- [ ] Update README.md with tenant information




## cURL proofs
This used the default implemenation.
```
curl http://localhost:8001/api/status
```

This uses the header field to set the tenant context
```
curl -H "X-Tenant-Id: WayneEnt" http://localhost:8001/api/status
```




Create a new user and get authtoken back
```
curl -X POST http://localhost:8001/api/register \
  -H "Content-Type: application/json" \
  -H "X-Tenant-Id: WayneEnt" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
  ```

  Response looks like:
  ```
  {"success":true,"message":"User registered successfully","data":{"user":{"name":"John Doe","email":"john@example.com","updated_at":"2025-11-13T09:41:42.000000Z","created_at":"2025-11-13T09:41:42.000000Z","id":2},"token":"1|KlI8iZujOLiOm3hA19EuBM0tc8HKyuJZ3iMRAqdi4929ad3a","tenant":"WayneEnt"}}%  
  ```