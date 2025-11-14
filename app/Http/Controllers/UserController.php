<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;
use App\Contracts\UserServiceInterface;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserServiceInterface $userService
    ) {
    }
    /**
     * Display a listing of users in the tenant's organizations.
     */
    public function index(Request $request)
    {

        $users = $this->userService->list($request);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        $currentUser = $request->user();

        $projects = $this->userService->createForm(($request));

        $organizations = $currentUser->organizations;

        return view('dashboard.users.create', compact('organizations', 'projects'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->userService->store($request);

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request, User $user)
    {
        $currentUser = $request->user();
        $organizations = $currentUser->organizations;

        // Ensure the user being edited belongs to one of the current user's organizations
        if (!$user->organizations->intersect($organizations)->count()) {
            abort(403, 'Unauthorized access to this user.');
        }

        $user->load(['organizations', 'projects.organization']);

        $projects = $this->userService->editForm($request);

        return view('dashboard.users.edit', compact('user', 'organizations', 'projects'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->userService->update($request, $user);

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        try {
            $this->userService->delete($request, $user);

            return redirect()
                ->route('dashboard.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (ValidationException $exception) {
            return redirect()
                ->route('dashboard.users.index')
                ->with('error', $exception->errors()['user'][0] ?? 'Unable to delete user.');
        }
    }
}
