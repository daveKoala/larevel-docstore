<?php

namespace App\Services;

use App\Mail\UserMessage;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * EmailService - Concrete class for sending emails
 *
 * This is a concrete service with NO interface.
 * It does NOT need to be registered in AppServiceProvider.
 * Laravel will auto-resolve it when injected into constructors.
 *
 * This service is NOT tenant-specific and will never be overridden.
 */
class EmailService
{
    /**
     * Send a simple text email
     */
    public function sendTextEmail(string $to, string $subject, string $body): bool
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject);
            });

            Log::info("Email sent to {$to}: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email with HTML content
     */
    public function sendHtmlEmail(string $to, string $subject, string $htmlBody): bool
    {
        try {
            Mail::html($htmlBody, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject);
            });

            Log::info("HTML email sent to {$to}: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send HTML email to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a formatted email to a user using Mailable with Blade template
     */
    public function sendUserMessage(User $user, string $subject, string $body): bool
    {
        try {
            Mail::to($user->email)->send(new UserMessage($user, $subject, $body));

            Log::info("User message sent to {$user->email}: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send user message to {$user->email}: " . $e->getMessage());
            return false;
        }
    }
}
