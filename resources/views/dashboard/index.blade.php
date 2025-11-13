@extends('layouts.tenant')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Organizations Card -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Organizations
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ $stats['organizations'] }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Card -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Projects
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ $stats['projects'] }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Card -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Users
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ $stats['users'] }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="{{ route('dashboard.users.index') }}" class="font-medium text-blue-700 hover:text-blue-900">
                    Manage users
                </a>
            </div>
        </div>
    </div>

    <!-- Orders Card -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Orders
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ $stats['orders'] }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
            <div class="text-sm">
                <a href="{{ route('dashboard.orders.index') }}" class="font-medium text-blue-700 hover:text-blue-900">
                    View orders
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mt-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Welcome, {{ auth()->user()->name }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                You have {{ auth()->user()->isAdmin() ? 'admin' : 'user' }} access to your organization.
            </p>
        </div>
    </div>
</div>

<!-- My Organizations -->
<div class="mt-8">
    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">My Organizations</h3>
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($organizations as $organization)
                <li class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">{{ $organization->name }}</p>
                            <p class="text-sm text-gray-500">{{ $organization->slug }}</p>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-6 py-4">
                    <p class="text-sm text-gray-500">You are not assigned to any organizations.</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>

<!-- My Projects -->
<div class="mt-8">
    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">My Projects</h3>
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($projects as $project)
                <li class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">{{ $project->name }}</p>
                            <p class="text-sm text-gray-500">{{ $project->organization->name }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                </li>
            @empty
                <li class="px-6 py-4">
                    <p class="text-sm text-gray-500">You are not assigned to any projects.</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
