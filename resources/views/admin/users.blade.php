@extends('layouts.tenant')

@section('title', 'Admin - Users')
@section('page-title', 'User Management')

@section('content')
<div class="mb-6">
    <p class="text-sm text-gray-600">Send emails to users using EmailService (concrete class - NO registration needed!)</p>
</div>

@if (session('success'))
    <div class="mb-6 rounded-md bg-green-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-md bg-red-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <ul class="divide-y divide-gray-200">
        @forelse($users as $user)
            <li>
                <div class="px-4 py-4 flex items-center justify-between sm:px-6 hover:bg-gray-50">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-lg">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $user->role === 'tenant_admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $user->role === 'tenant_user' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        {{ $user->role }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                @if($user->organizations->count() > 0)
                                    <p class="text-xs text-gray-400 mt-1">
                                        Organizations: {{ $user->organizations->pluck('name')->join(', ') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="ml-5 flex-shrink-0">
                        <a href="{{ route('admin.users.email', $user) }}"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Send Email
                        </a>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-8 text-center text-gray-500">
                No users found.
            </li>
        @endforelse
    </ul>
</div>

@if($users->hasPages())
    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endif

<div class="mt-6 p-4 bg-blue-50 rounded-md">
    <h3 class="text-sm font-medium text-blue-800 mb-2">MailPit Info</h3>
    <p class="text-sm text-blue-700">All emails are captured in MailPit for testing.</p>
    <a href="http://localhost:8026" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 underline">
        Open MailPit Web UI →
    </a>
</div>
@endsection
