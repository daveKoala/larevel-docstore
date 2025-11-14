<?php

namespace App\Services;

use App\Contracts\UserServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{

    public function list(Request $request): Collection
    {
        $currentUser = $request->user();

        $organizationIds = $currentUser->organizations->pluck('id');

        return  User::whereHas('organizations', function ($query) use ($organizationIds) {
            $query->whereIn('organizations.id', $organizationIds);
        })
            ->with('organizations')
            ->withCount(['organizations', 'projects'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createForm(Request $request): Collection
    {
        $currentUser = $request->user();

        $organizations = $currentUser->organizations;

        return Project::whereIn('organization_id', $organizations->pluck('id'))
            ->with('organization')
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request): void
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
    }

    public function editForm(Request $request): Collection
    {
        $currentUser = $request->user();
        $organizations = $currentUser->organizations;

        return Project::whereIn('organization_id', $organizations->pluck('id'))
            ->with('organization')
            ->orderBy('name')
            ->get();
    }

    public function update(REquest $request, User $user): void {
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

    }

    public function delete(Request $request, User $user): void {
        $currentUser = $request->user();

        // Ensure the user being deleted belongs to one of the current user's organizations
        if (!$user->organizations->intersect($currentUser->organizations)->count()) {
            abort(403, 'Unauthorized access to this user.');
        }

        // Prevent deleting yourself
        if ($user->id === $currentUser->id) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete yourself.',
            ]);
        }

        $user->delete();
    }
}
