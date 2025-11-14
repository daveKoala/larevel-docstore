@extends('layouts.admin')

@section('title', 'Send Email')
@section('page-title', 'Send Email to ' . $user->name)

@section('content')
<div class="max-w-2xl">
    <div class="mb-6 p-4 bg-yellow-50 rounded-md">
        <h3 class="text-sm font-medium text-yellow-800 mb-2">Using EmailService (Concrete Class)</h3>
        <p class="text-sm text-yellow-700">
            This form uses <code class="bg-yellow-100 px-1 rounded">EmailService</code> directly (NO interface, NO registration needed).
            Laravel auto-resolves it via dependency injection.
        </p>
    </div>

    <form action="{{ route('admin.users.email.send', $user) }}" method="POST" class="space-y-6">
        @csrf

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
                    <label class="block text-sm font-medium text-gray-700">Recipient</label>
                    <div class="mt-1 p-3 bg-gray-50 rounded-md">
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="subject"
                           id="subject"
                           required
                           value="{{ old('subject') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           placeholder="Enter email subject">
                </div>

                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea name="body"
                              id="body"
                              rows="10"
                              required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                              placeholder="Enter your message here...">{{ old('body') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Plain text email will be sent.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Send Email
            </button>
        </div>
    </form>

    <div class="mt-6 p-4 bg-blue-50 rounded-md">
        <h3 class="text-sm font-medium text-blue-800 mb-2">MailPit - Email Testing</h3>
        <p class="text-sm text-blue-700 mb-2">Emails will be captured by MailPit (not actually sent).</p>
        <a href="http://localhost:8026" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 underline">
            Open MailPit Web UI to view emails →
        </a>
    </div>
</div>
@endsection
