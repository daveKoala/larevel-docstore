<?php

namespace App\Customers\AcMe;

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService implements OrderServiceInterface
{
    /**
     * Get paginated orders accessible to the user
     */
    public function getOrders(User $user, int $perPage): LengthAwarePaginator
    {
        return Order::accessibleByUser($user)
            ->with(['user:id,name,email', 'project:id,guid,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new order - AcMe custom: prefix with tenant name
     */
    public function createOrder(User $user, int $projectId, string $details): Order
    {
        // AcMe Corporation customization: Prefix order details with tenant name
        $prefixedDetails = "[AcMe] " . $details;

        return Order::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'details' => $prefixedDetails,
        ]);
    }

    /**
     * Get a single order by GUID
     */
    public function getOrderByGuid(string $guid): ?Order
    {
        return Order::where('guid', $guid)
            ->with(['user:id,name,email', 'project:id,guid,name'])
            ->first();
    }

    /**
     * Update an order - AcMe custom: ensure prefix is maintained
     */
    public function updateOrder(Order $order, int $projectId, string $details): Order
    {
        // AcMe Corporation customization: Maintain the prefix
        $prefixedDetails = "[AcMe] " . $details;

        $order->update([
            'project_id' => $projectId,
            'details' => $prefixedDetails,
        ]);

        return $order->fresh();
    }

    /**
     * Delete an order
     */
    public function deleteOrder(Order $order): bool
    {
        return $order->delete();
    }
}
