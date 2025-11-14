<?php

namespace App\Contracts;

use App\Models\Order;

/**
 * NotificationServiceInterface - Contract for tenant-specific notifications
 *
 * This interface allows tenants to customize how notifications are sent.
 * For example:
 * - Default tenant: Send simple email
 * - AcMe tenant: Send email + SMS + Slack notification
 * - Beta tenant: Use default OR customize email template
 */
interface NotificationServiceInterface
{
    /**
     * Send notification when an order is created
     */
    public function notifyOrderCreated(Order $order): void;

    /**
     * Send notification when an order is updated
     */
    public function notifyOrderUpdated(Order $order): void;
}
