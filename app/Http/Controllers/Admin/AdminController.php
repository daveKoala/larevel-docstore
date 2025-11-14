<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;

/**
 * AdminController - Demonstrates EmailService usage (concrete class, NO registration needed)
 *
 * This controller injects EmailService directly without an interface.
 * Laravel auto-resolves it because it's a concrete class.
 * NO registration in AppServiceProvider required!
 */
class AdminController extends Controller
{
    public function __construct(
        private EmailService $emailService
    ) {}

    /**
     * Display list of users
     */
    public function users()
    {
        // Only super_admin can access
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Only super admins can access this page.');
        }

        $users = User::with('organizations')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Show email form for a specific user
     */
    public function showEmailForm(User $user)
    {
        // Only super_admin can access
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Only super admins can access this page.');
        }

        return view('admin.send-email', compact('user'));
    }

    /**
     * Send email to a user using EmailService (concrete class, auto-resolved)
     */
    public function sendEmail(Request $request, User $user)
    {
        // Only super_admin can access
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Only super admins can access this page.');
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        // Use EmailService directly - no interface, no registration needed!
        $success = $this->emailService->sendTextEmail(
            $user->email,
            $validated['subject'],
            $validated['body']
        );

        if ($success) {
            return redirect()
                ->route('admin.users')
                ->with('success', "Email sent to {$user->name} ({$user->email})");
        }

        return redirect()
            ->route('admin.users')
            ->with('error', 'Failed to send email. Check logs for details.');
    }
}
