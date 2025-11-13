<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of projects accessible to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $projects = $request->user()
            ->projects()
            ->with('organization:id,name,slug')
            ->get(['id', 'guid', 'name', 'description', 'status', 'organization_id']);

        return $this->successResponse($projects, 'Projects retrieved successfully');
    }
}
