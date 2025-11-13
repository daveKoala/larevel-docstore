<?php

namespace App\Services;

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
     * Create a new order
     */
    public function createOrder(User $user, int $projectId, string $details): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'details' => $details,
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
     * Update an order
     */
    public function updateOrder(Order $order, int $projectId, string $details): Order
    {
        $order->update([
            'project_id' => $projectId,
            'details' => $details,
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
