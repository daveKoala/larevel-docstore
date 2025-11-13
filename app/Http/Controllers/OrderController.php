<?php

namespace App\Http\Controllers;

use App\Contracts\OrderServiceInterface;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Project;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private OrderServiceInterface $orderService
    ) {
    }

    /**
     * Display a listing of orders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = config('app.pagination_per_page', 10);

        $orders = $this->orderService->getOrders($request->user(), $perPage);

        return $this->paginatedResponse($orders, 'Orders retrieved successfully');
    }

    /**
     * Show the form for creating a new resource.
     * Not needed for API, return not implemented.
     */
    public function create(): JsonResponse
    {
        return $this->notImplementedResponse('Create form not needed for API');
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        // Get the project from the validated GUID
        $project = Project::where('guid', $request->input('project_guid'))->firstOrFail();

        // Create the order via service (tenant-specific logic applied here)
        $order = $this->orderService->createOrder(
            $request->user(),
            $project->id,
            $request->input('details')
        );

        // Load relationships
        $order->load(['user:id,name,email', 'project:id,guid,name']);

        return $this->successResponse($order, 'Order created successfully', 201);
    }

    /**
     * Display the specified order by GUID.
     */
    public function show(Request $request, string $guid): JsonResponse
    {
        $order = $this->orderService->getOrderByGuid($guid);

        if (!$order) {
            return $this->notFoundResponse('Order not found');
        }

        // Check authorization - user must have access to the project
        if (!$request->user()->projects()->where('projects.id', $order->project_id)->exists()) {
            return $this->unauthorizedResponse('You do not have access to this order');
        }

        return $this->successResponse($order, 'Order retrieved successfully');
    }

    /**
     * Show the form for editing the specified resource.
     * Not needed for API, return not implemented.
     */
    public function edit(string $guid): JsonResponse
    {
        return $this->notImplementedResponse('Edit form not needed for API');
    }

    /**
     * Update the specified order.
     * Not implemented yet, return success with message.
     */
    public function update(Request $request, string $guid): JsonResponse
    {
        return $this->notImplementedResponse('Update functionality not yet implemented');
    }

    /**
     * Remove the specified order.
     * Not implemented yet, return success with message.
     */
    public function destroy(string $guid): JsonResponse
    {
        return $this->notImplementedResponse('Delete functionality not yet implemented');
    }
}
