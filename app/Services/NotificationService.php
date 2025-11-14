<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Models\Order;
use App\Services\EmailService;

/**
 * NotificationService - Default implementation for order notifications
 *
 * This is the DEFAULT implementation used by all tenants unless overridden.
 * It uses the EmailService (concrete class) to send simple notifications.
 *
 * Tenants can override this by creating their own implementation:
 * - app/Customers/AcMe/NotificationService.php
 * - app/Customers/Beta/NotificationService.php
 */
class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private EmailService $emailService
    ) {}

    /**
     * Send notification when an order is created
     */
    public function notifyOrderCreated(Order $order): void
    {
        $order->load(['user', 'project']);

        $subject = "New Order Created - {$order->project->name}";
        $body = "Hello {$order->user->name},\n\n"
            . "Your order has been created successfully.\n\n"
            . "Project: {$order->project->name}\n"
            . "Details: {$order->details}\n"
            . "Created: {$order->created_at->format('Y-m-d H:i:s')}\n\n"
            . "Thank you!";

        $this->emailService->sendTextEmail(
            $order->user->email,
            $subject,
            $body
        );
    }

    /**
     * Send notification when an order is updated
     */
    public function notifyOrderUpdated(Order $order): void
    {
        $order->load(['user', 'project']);

        $subject = "Order Updated - {$order->project->name}";
        $body = "Hello {$order->user->name},\n\n"
            . "Your order has been updated.\n\n"
            . "Project: {$order->project->name}\n"
            . "Details: {$order->details}\n"
            . "Updated: {$order->updated_at->format('Y-m-d H:i:s')}\n\n"
            . "Thank you!";

        $this->emailService->sendTextEmail(
            $order->user->email,
            $subject,
            $body
        );
    }
}
