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
     * Send a formatted email to a user (QUEUED - Default)
     * Use this for most emails - processed in background
     */
    public function sendUserMessage(User $user, string $subject, string $body): void
    {
        Mail::to($user->email)->queue(new UserMessage($user, $subject, $body));
        Log::info("User message queued for {$user->email}: {$subject}");
    }

    /**
     * Send a formatted email to a user (IMMEDIATE)
     * Use this for critical/urgent emails (password resets, 2FA codes)
     */
    public function sendUserMessageNow(User $user, string $subject, string $body): bool
    {
        try {
            Mail::to($user->email)->send(new UserMessage($user, $subject, $body));

            Log::info("User message sent immediately to {$user->email}: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send user message to {$user->email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a simple text email (QUEUED)
     */
    public function sendTextEmail(string $to, string $subject, string $body): void
    {
        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });

        Log::info("Text email queued for {$to}: {$subject}");
    }

    /**
     * Send a simple text email (IMMEDIATE)
     */
    public function sendTextEmailNow(string $to, string $subject, string $body): bool
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            Log::info("Text email sent immediately to {$to}: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send text email to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email with HTML content (QUEUED)
     */
    public function sendHtmlEmail(string $to, string $subject, string $htmlBody): void
    {
        Mail::html($htmlBody, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });

        Log::info("HTML email queued for {$to}: {$subject}");
    }

    /**
     * Send email with HTML content (IMMEDIATE)
     */
    public function sendHtmlEmailNow(string $to, string $subject, string $htmlBody): bool
    {
        try {
            Mail::html($htmlBody, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            Log::info("HTML email sent immediately to {$to}: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send HTML email to {$to}: " . $e->getMessage());
            return false;
        }
    }
}
