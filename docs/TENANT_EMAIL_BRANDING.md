# Tenant Email Branding System

## Overview

The application uses a database-backed system for tenant-specific email branding. Each tenant can have custom logos, banners, colors, and CC rules for their emails.

## How It Works

### 1. Tenant Resolution
Emails use the **recipient user's organization** to determine branding:
- When sending an email to a user, the system looks up their organization
- Loads the email config for that organization's tenant slug
- Applies the branding to the email template

### 2. Configuration Storage
Tenant email configurations are stored in the `tenant_email_configs` table with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `tenant_slug` | string | Organization slug (e.g., 'acme', 'wayneent', 'beta') |
| `logo_url` | string | URL to tenant's logo image |
| `banner_image_url` | string | URL to tenant's banner image |
| `primary_color` | string | Hex color code for branding (e.g., '#dc2626') |
| `header_text` | string | Company name shown in email header |
| `footer_text` | string | Custom footer text |
| `support_email` | string | Support contact email |
| `cc_emails` | JSON | Array of email addresses to CC on all emails |

### 3. Default Fallbacks
If a tenant doesn't have a custom configuration:
- **Banner**: Uses `public/images/bookshelf.jpg`
- **Logo**: No logo shown
- **Primary Color**: Blue (#2563eb)
- **Header Text**: Application name from config
- **Footer Text**: Generic support message

## Current Tenant Configurations

### AcMe Corporation
- **Color**: Red (#dc2626)
- **Banner**: Default bookshelf.jpg
- **CC Emails**: None
- **Footer**: "AcMe Corp - Quality Products Since 1949. For questions, contact your account manager."

### Wayne Enterprises
- **Color**: Dark (#1a1a1a)
- **Banner**: Default bookshelf.jpg
- **CC Emails**: admin@wayneent.com, notifications@wayneent.com
- **Footer**: "Wayne Enterprises - Protecting Gotham Since 1939. Confidential and Proprietary."

### Beta Company
- **Color**: Purple (#7c3aed)
- **Banner**: Default bookshelf.jpg
- **CC Emails**: None
- **Footer**: "Thank you for being part of our beta program!"

## Adding Custom Images

### Step 1: Add Image Files
Place your tenant-specific images in `public/images/`:

```
public/images/
  ├── bookshelf.jpg           # Default banner (exists)
  ├── acme-logo.png           # Add this for AcMe logo
  ├── acme-banner.jpg         # Add this for AcMe banner
  ├── wayneent-logo.png       # Add this for Wayne logo
  ├── wayneent-banner.jpg     # Add this for Wayne banner
  └── beta-logo.png           # Add this for Beta logo (optional)
```

### Step 2: Update Database
Update the tenant config via Tinker or create a migration:

```php
php artisan tinker

$config = \App\Models\TenantEmailConfig::where('tenant_slug', 'acme')->first();
$config->update([
    'logo_url' => asset('images/acme-logo.png'),
    'banner_image_url' => asset('images/acme-banner.jpg'),
]);

// Clear cache
app(\App\Services\TenantEmailConfigService::class)->clearCache('acme');
```

### Step 3: Image Recommendations
- **Logos**: ~150x50px (or similar aspect ratio)
- **Banners**: ~600x200px (or similar aspect ratio, wide and short)
- **Format**: PNG for logos (transparent background), JPG for banners
- **File size**: Keep under 200KB for email compatibility

## Updating Tenant Configurations

### Via Database
```sql
UPDATE tenant_email_configs
SET logo_url = 'http://localhost:8001/images/acme-logo.png',
    banner_image_url = 'http://localhost:8001/images/acme-banner.jpg',
    primary_color = '#dc2626'
WHERE tenant_slug = 'acme';
```

### Via Seeder
Edit `database/seeders/TenantEmailConfigSeeder.php` and run:
```bash
php artisan db:seed --class=TenantEmailConfigSeeder
php artisan cache:clear
```

### Via Code
```php
use App\Models\TenantEmailConfig;
use App\Services\TenantEmailConfigService;

TenantEmailConfig::updateOrCreate(
    ['tenant_slug' => 'acme'],
    [
        'logo_url' => asset('images/acme-logo.png'),
        'banner_image_url' => asset('images/acme-banner.jpg'),
        'primary_color' => '#dc2626',
        'header_text' => 'AcMe Corporation',
    ]
);

// Clear cache
app(TenantEmailConfigService::class)->clearCache('acme');
```

## Email Components

The system uses reusable Blade components:

- `x-email.layout` - Main email wrapper with banner and styles
- `x-email.header` - Logo and company name section
- `x-email.footer` - Footer with support info

### Creating New Email Types
To create a new email type that uses tenant branding:

```php
// In your Mailable class
use App\Services\TenantEmailConfigService;

class OrderConfirmation extends Mailable
{
    public array $tenantConfig;

    public function __construct(public Order $order)
    {
        $user = $order->user;
        $tenant = $user->organizations()->first()->slug ?? config('app.default_tenant');

        $configService = app(TenantEmailConfigService::class);
        $this->tenantConfig = $configService->getConfig($tenant);
    }
}
```

```blade
{{-- resources/views/emails/order-confirmation.blade.php --}}
<x-email.layout :config="$tenantConfig" :emailSubject="'Order Confirmation'">
    <p>Thank you for your order #{{ $order->id }}</p>
    <!-- Your email content here -->
</x-email.layout>
```

## Caching

Tenant email configurations are cached for 1 hour to improve performance. To clear the cache:

```php
// Clear specific tenant
app(\App\Services\TenantEmailConfigService::class)->clearCache('acme');

// Clear all application cache (includes tenant configs)
php artisan cache:clear
```

## Testing

Test emails with different tenant branding:

```bash
php artisan tinker
```

```php
$betaUser = User::where('email', 'admin@beta.com')->first();
Mail::to($betaUser)->send(new UserMessage($betaUser, 'Test', 'Hello!'));
// Check MailPit at http://localhost:8026
```

## Troubleshooting

### Issue: Wrong branding showing
- **Cause**: Cache not cleared after config update
- **Fix**: `php artisan cache:clear`

### Issue: Broken images in email
- **Cause**: Image files don't exist at specified path
- **Fix**: Set `logo_url` or `banner_image_url` to `null` to use defaults, or add the image files

### Issue: All emails use same branding
- **Cause**: Users don't have organization associations
- **Fix**: Ensure users are linked to organizations via the `organization_user` pivot table

### Issue: CC not working
- **Cause**: `cc_emails` field is not a valid JSON array
- **Fix**: Ensure format is `["email1@example.com", "email2@example.com"]`
