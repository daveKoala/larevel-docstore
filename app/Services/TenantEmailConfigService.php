<?php

namespace App\Services;

use App\Models\TenantEmailConfig;
use Illuminate\Support\Facades\Cache;

/**
 * TenantEmailConfigService - Retrieves tenant-specific email configurations
 *
 * This is a concrete service with NO interface.
 * It does NOT need to be registered in AppServiceProvider.
 * Laravel will auto-resolve it when injected into constructors.
 */
class TenantEmailConfigService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'tenant_email_config:';

    /**
     * Get tenant email configuration with caching
     */
    public function getConfig(string $tenantSlug): array
    {
        $cacheKey = self::CACHE_PREFIX . strtolower($tenantSlug);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantSlug) {
            $config = TenantEmailConfig::getByTenant($tenantSlug);

            if ($config) {
                return $config->toConfigArray();
            }

            return $this->getDefaultConfig();
        });
    }

    /**
     * Clear cache for a specific tenant
     */
    public function clearCache(string $tenantSlug): void
    {
        $cacheKey = self::CACHE_PREFIX . strtolower($tenantSlug);
        Cache::forget($cacheKey);
    }

    /**
     * Get default configuration when tenant config doesn't exist
     */
    private function getDefaultConfig(): array
    {
        return [
            'logo_url' => null,
            'banner_image_url' => asset('images/bookshelf.jpg'),
            'primary_color' => '#2563eb',
            'header_text' => config('app.name', 'Laravel'),
            'footer_text' => 'If you have any questions, please contact your administrator.',
            'support_email' => config('mail.from.address'),
            'cc_emails' => [],
        ];
    }

    /**
     * Get CC email addresses for a tenant
     */
    public function getCcEmails(string $tenantSlug): array
    {
        $config = $this->getConfig($tenantSlug);
        return $config['cc_emails'] ?? [];
    }
}
