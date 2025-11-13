<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    /**
     * Get paginated orders accessible to the user
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrders(User $user, int $perPage): LengthAwarePaginator;

    /**
     * Create a new order
     *
     * @param User $user
     * @param int $projectId
     * @param string $details
     * @return Order
     */
    public function createOrder(User $user, int $projectId, string $details): Order;

    /**
     * Get a single order by GUID
     *
     * @param string $guid
     * @return Order|null
     */
    public function getOrderByGuid(string $guid): ?Order;

    /**
     * Update an order
     *
     * @param Order $order
     * @param int $projectId
     * @param string $details
     * @return Order
     */
    public function updateOrder(Order $order, int $projectId, string $details): Order;

    /**
     * Delete an order
     *
     * @param Order $order
     * @return bool
     */
    public function deleteOrder(Order $order): bool;
}
