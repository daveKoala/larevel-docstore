<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users in the tenant's organizations.
     */
    public function index(Request $request)
    {
        $currentUser = $request->user();
        $organizationIds = $currentUser->organizations->pluck('id');

        $users = User::whereHas('organizations', function ($query) use ($organizationIds) {
            $query->whereIn('organizations.id', $organizationIds);
        })
        ->with('organizations')
        ->withCount(['organizations', 'projects'])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        $currentUser = $request->user();
        $organizations = $currentUser->organizations;
        $projects = Project::whereIn('organization_id', $organizations->pluck('id'))
            ->with('organization')
            ->orderBy('name')
            ->get();

        return view('dashboard.users.create', compact('organizations', 'projects'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();
        $organizationIds = $currentUser->organizations->pluck('id');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:tenant_admin,tenant_user'],
            'organizations' => ['required', 'array', 'min:1'],
            'organizations.*' => ['exists:organizations,id', Rule::in($organizationIds)],
            'projects' => ['nullable', 'array'],
            'projects.*' => ['exists:projects,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Attach organizations
        $user->organizations()->attach($validated['organizations']);

        // Attach projects
        if (!empty($validated['projects'])) {
            $user->projects()->attach($validated['projects']);
        }

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
        $projects = Project::whereIn('organization_id', $organizations->pluck('id'))
            ->with('organization')
            ->orderBy('name')
            ->get();

        return view('dashboard.users.edit', compact('user', 'organizations', 'projects'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();
        $organizationIds = $currentUser->organizations->pluck('id');

        // Ensure the user being updated belongs to one of the current user's organizations
        if (!$user->organizations->intersect($currentUser->organizations)->count()) {
            abort(403, 'Unauthorized access to this user.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:tenant_admin,tenant_user'],
            'organizations' => ['required', 'array', 'min:1'],
            'organizations.*' => ['exists:organizations,id', Rule::in($organizationIds)],
            'projects' => ['nullable', 'array'],
            'projects.*' => ['exists:projects,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync organizations
        $user->organizations()->sync($validated['organizations']);

        // Sync projects
        $user->projects()->sync($validated['projects'] ?? []);

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Ensure the user being deleted belongs to one of the current user's organizations
        if (!$user->organizations->intersect($currentUser->organizations)->count()) {
            abort(403, 'Unauthorized access to this user.');
        }

        // Prevent deleting yourself
        if ($user->id === $currentUser->id) {
            return redirect()
                ->route('dashboard.users.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()
            ->route('dashboard.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
