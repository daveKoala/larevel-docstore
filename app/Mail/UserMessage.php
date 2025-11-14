<?php

namespace App\Mail;

use App\Models\User;
use App\Services\TenantEmailConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserMessage extends Mailable
{
    use Queueable, SerializesModels;

    public array $tenantConfig;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 120;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $emailSubject,
        public string $messageBody,
        private ?TenantEmailConfigService $configService = null
    ) {
        // Resolve service if not injected (for backward compatibility)
        $this->configService ??= app(TenantEmailConfigService::class);

        // Get tenant from recipient user's organization (not current request context)
        $tenant = $this->getRecipientTenant();
        $this->tenantConfig = $this->configService->getConfig($tenant);

        // Set queue name for this job
        $this->onQueue('emails');
    }

    /**
     * Get tenant slug from recipient user's organization
     */
    private function getRecipientTenant(): string
    {
        // Try to get tenant from user's first organization
        if ($this->user->organizations()->exists()) {
            return $this->user->organizations()->first()->slug;
        }

        // Fallback to default tenant if user has no organization
        return config('app.default_tenant', 'acme');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $ccAddresses = [];

        // Add CC addresses from tenant config
        foreach ($this->tenantConfig['cc_emails'] as $ccEmail) {
            $ccAddresses[] = new Address($ccEmail);
        }

        return new Envelope(
            subject: $this->emailSubject,
            cc: $ccAddresses,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-message',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
