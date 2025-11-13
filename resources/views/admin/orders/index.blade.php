@extends('layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')
<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <p class="mt-2 text-sm text-gray-700">A list of all orders in the system (view-only).</p>
    </div>
</div>

<div class="mt-8 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">GUID</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Project</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Organization</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Details</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">View</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($orders as $order)
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono text-gray-500 sm:pl-6">
                                {{ substr($order->guid, 0, 12) }}...
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                {{ $order->user->name }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                {{ $order->project->name }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ $order->project->organization->name }}
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $order->details }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ $order->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-sm text-gray-500 text-center">
                                No orders found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($orders->hasPages())
<div class="mt-4">
    {{ $orders->links() }}
</div>
@endif
@endsection
