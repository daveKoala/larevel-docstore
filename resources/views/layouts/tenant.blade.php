<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Order Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="h-16 flex items-center justify-center border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">Tenant Portal</h1>
            </div>

            <nav class="mt-6">
                <a href="{{ route('dashboard.index') }}"
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 {{ request()->routeIs('dashboard.index') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('dashboard.users.index') }}"
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 {{ request()->routeIs('dashboard.users.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Users
                </a>

                <a href="{{ route('dashboard.orders.index') }}"
                   class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 {{ request()->routeIs('dashboard.orders.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Orders
                </a>
            </nav>

            <div class="absolute bottom-0 w-64 p-6 border-t border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-semibold text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                        Sign out
                    </button>
                </form>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <!-- Top bar -->
            <div class="h-16 bg-white shadow-sm flex items-center justify-between px-8">
                <h2 class="text-2xl font-semibold text-gray-800">@yield('page-title')</h2>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-auto p-8">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
