<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Order Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Order Management System
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Sign in to your account
                </p>
            </div>

            <form class="mt-8 space-y-6" action="{{ route('login.post') }}" method="POST">
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
                                    {{ $errors->first() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                @endif

                <input type="hidden" name="remember" value="true">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Email address" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Password">
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sign in
                    </button>
                </div>

                <div class="mt-6 border-t border-gray-200 pt-6">
                    <p class="text-xs text-gray-500 text-center mb-3">Quick Login (Testing Only):</p>
                    <div class="space-y-2">
                        <button type="button" onclick="fillLogin('admin@system.com', 'password')"
                                class="w-full px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Login as Super Admin
                        </button>
                        <button type="button" onclick="fillLogin('wile@acme.com', 'password')"
                                class="w-full px-3 py-2 text-xs font-medium text-gray-700 bg-orange-50 border border-orange-300 rounded-md hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Login as AcMe Tenant (wile@acme.com)
                        </button>
                        <button type="button" onclick="fillLogin('admin@beta.com', 'password')"
                                class="w-full px-3 py-2 text-xs font-medium text-gray-700 bg-blue-50 border border-blue-300 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Login as Beta Tenant (admin@beta.com)
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 text-center mt-3">Click button to auto-fill credentials</p>
                </div>
            </form>

            <script>
                function fillLogin(email, password) {
                    document.getElementById('email').value = email;
                    document.getElementById('password').value = password;
                    // Optional: Focus on submit button for immediate submission
                    document.querySelector('button[type="submit"]').focus();
                }
            </script>
        </div>
    </div>
</body>
</html>
