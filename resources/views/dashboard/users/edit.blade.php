@extends('layouts.tenant')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('dashboard.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            There were errors with your submission
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">Leave blank to keep current password.</p>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role"
                            id="role"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">Select a role</option>
                        <option value="tenant_admin" {{ old('role', $user->role) === 'tenant_admin' ? 'selected' : '' }}>Tenant Admin</option>
                        <option value="tenant_user" {{ old('role', $user->role) === 'tenant_user' ? 'selected' : '' }}>Tenant User</option>
                    </select>
                </div>

                <div>
                    <label for="organizations" class="block text-sm font-medium text-gray-700">
                        Organizations <span class="text-red-500">*</span>
                    </label>
                    <select name="organizations[]"
                            id="organizations"
                            multiple
                            size="4"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @foreach($organizations as $organization)
                            <option value="{{ $organization->id }}"
                                {{ in_array($organization->id, old('organizations', $user->organizations->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $organization->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple.</p>
                </div>

                <div>
                    <label for="projects" class="block text-sm font-medium text-gray-700">
                        Projects
                    </label>
                    <select name="projects[]"
                            id="projects"
                            multiple
                            size="5"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                {{ in_array($project->id, old('projects', $user->projects->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $project->name }} ({{ $project->organization->name }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Hold Ctrl/Cmd to select multiple.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('dashboard.users.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Update User
            </button>
        </div>
    </form>
</div>
@endsection
