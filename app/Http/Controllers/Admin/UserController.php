<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private EmailService $emailService
    ) {}

    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('organizations')
            ->withCount(['organizations', 'projects'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $organizations = Organization::orderBy('name')->get();
        $projects = Project::with('organization')->orderBy('name')->get();

        return view('admin.users.create', compact('organizations', 'projects'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin,tenant_admin,tenant_user'],
            'organizations' => ['nullable', 'array'],
            'organizations.*' => ['exists:organizations,id'],
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
        if (!empty($validated['organizations'])) {
            $user->organizations()->attach($validated['organizations']);
        }

        // Attach projects
        if (!empty($validated['projects'])) {
            $user->projects()->attach($validated['projects']);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['organizations', 'projects.organization']);
        $organizations = Organization::orderBy('name')->get();
        $projects = Project::with('organization')->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'organizations', 'projects'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin,tenant_admin,tenant_user'],
            'organizations' => ['nullable', 'array'],
            'organizations.*' => ['exists:organizations,id'],
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
        $user->organizations()->sync($validated['organizations'] ?? []);

        // Sync projects
        $user->projects()->sync($validated['projects'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show email form for a specific user
     */
    public function showEmailForm(User $user)
    {
        return view('admin.users.email', compact('user'));
    }

    /**
     * Send email to a user using EmailService (concrete class, auto-resolved)
     * Uses Mailable with Blade template for professional formatting
     * Emails are queued for background processing
     */
    public function sendEmail(Request $request, User $user)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        try {
            // Queue email for background processing
            $this->emailService->sendUserMessage(
                $user,
                $validated['subject'],
                $validated['body']
            );

            return redirect()
                ->route('admin.users.email', $user)
                ->with('success', "Email queued for {$user->name} ({$user->email})");
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.email', $user)
                ->with('error', 'Failed to queue email. Check logs for details.');
        }
    }
}
