<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the tenant dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get user's organizations
        $organizations = $user->organizations;

        // Get user's projects
        $projects = $user->projects()->with('organization')->get();

        // Get stats
        $stats = [
            'organizations' => $organizations->count(),
            'projects' => $projects->count(),
            'users' => User::whereHas('organizations', function ($query) use ($user) {
                $query->whereIn('organizations.id', $user->organizations->pluck('id'));
            })->count(),
            'orders' => Order::whereHas('project', function ($query) use ($user) {
                $query->whereIn('projects.id', $user->projects->pluck('id'));
            })->count(),
        ];

        return view('dashboard.index', compact('organizations', 'projects', 'stats'));
    }
}
