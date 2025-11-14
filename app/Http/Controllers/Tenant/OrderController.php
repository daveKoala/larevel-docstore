<?php

namespace App\Http\Controllers\Tenant;

use App\Contracts\NotificationServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderServiceInterface $orderService,
        private NotificationServiceInterface $notificationService
    ) {
    }
    /**
     * Display a listing of orders accessible to the current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::whereHas('project', function ($query) use ($user) {
            $query->whereIn('projects.id', $user->projects->pluck('id'));
        })
        ->with(['user', 'project.organization'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('dashboard.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $projects = $user->projects()->with('organization')->get();

        return view('dashboard.orders.create', compact('projects'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $projectIds = $user->projects->pluck('id');

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id', 'in:' . $projectIds->implode(',')],
            'details' => ['required', 'string'],
        ]);

        // Use service to create order (tenant-specific logic applied here)
        $order = $this->orderService->createOrder(
            $user,
            $validated['project_id'],
            $validated['details']
        );

        // Send notification (tenant-specific notification logic applied here)
        $this->notificationService->notifyOrderCreated($order);

        return redirect()
            ->route('dashboard.orders.index')
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        // Ensure the order belongs to one of the user's projects
        if (!$user->projects->contains($order->project_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['user', 'project.organization']);

        return view('dashboard.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Request $request, Order $order)
    {
        $user = $request->user();

        // Ensure the order belongs to one of the user's projects
        if (!$user->projects->contains($order->project_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['project']);
        $projects = $user->projects()->with('organization')->get();

        return view('dashboard.orders.edit', compact('order', 'projects'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $user = $request->user();
        $projectIds = $user->projects->pluck('id');

        // Ensure the order belongs to one of the user's projects
        if (!$user->projects->contains($order->project_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id', 'in:' . $projectIds->implode(',')],
            'details' => ['required', 'string'],
        ]);

        // Use service to update order (tenant-specific logic applied here)
        $order = $this->orderService->updateOrder(
            $order,
            $validated['project_id'],
            $validated['details']
        );

        // Send notification (tenant-specific notification logic applied here)
        $this->notificationService->notifyOrderUpdated($order);

        return redirect()
            ->route('dashboard.orders.index')
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Request $request, Order $order)
    {
        $user = $request->user();

        // Ensure the order belongs to one of the user's projects
        if (!$user->projects->contains($order->project_id)) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->delete();

        return redirect()
            ->route('dashboard.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
