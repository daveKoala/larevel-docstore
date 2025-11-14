<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantEmailConfig extends Model
{
    protected $fillable = [
        'tenant_slug',
        'logo_url',
        'banner_image_url',
        'primary_color',
        'header_text',
        'footer_text',
        'support_email',
        'cc_emails',
    ];

    protected $casts = [
        'cc_emails' => 'array',
    ];

    /**
     * Get config by tenant slug
     */
    public static function getByTenant(string $tenantSlug): ?self
    {
        return self::where('tenant_slug', strtolower($tenantSlug))->first();
    }

    /**
     * Get config as array with defaults
     */
    public function toConfigArray(): array
    {
        return [
            'logo_url' => $this->logo_url,
            'banner_image_url' => $this->banner_image_url ?? asset('images/bookshelf.jpg'),
            'primary_color' => $this->primary_color ?? '#2563eb',
            'header_text' => $this->header_text ?? config('app.name'),
            'footer_text' => $this->footer_text,
            'support_email' => $this->support_email,
            'cc_emails' => $this->cc_emails ?? [],
        ];
    }
}
