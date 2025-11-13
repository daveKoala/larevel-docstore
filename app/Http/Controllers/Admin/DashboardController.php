<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'organizations' => Organization::count(),
            'projects' => Project::count(),
            'users' => User::count(),
            'orders' => Order::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
