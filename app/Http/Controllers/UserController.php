<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('user.view');
        
        $users = User::with('roles')
            ->withCount('invoices')
            ->latest()
            ->paginate(10);
        
        return response()->json($users);
    }

    public function invite(Request $request)
    {
        $this->authorize('user.create');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|exists:roles,name',
            'send_invitation' => 'boolean'
        ]);

        return DB::transaction(function () use ($request) {
            // Generate temporary password
            $tempPassword = Str::random(12);
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($tempPassword),
                'email_verified_at' => null, // Force email verification
                'invited_by' => auth()->id(),
                'invited_at' => now(),
            ]);

            $user->assignRole($request->role);

            // Send invitation email if requested
            if ($request->boolean('send_invitation')) {
                $user->notify(new UserInvitation($tempPassword, $request->role));
                
                $user->update(['invitation_sent_at' => now()]);
            }

            return response()->json([
                'user' => $user->load('roles'),
                'temp_password' => $request->boolean('send_invitation') ? null : $tempPassword,
                'message' => $request->boolean('send_invitation') 
                    ? 'User invited successfully and invitation email sent.' 
                    : 'User created successfully. Please share the temporary password.'
            ], 201);
        });
    }

    public function store(Request $request)
    {
        $this->authorize('user.create');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|exists:roles,name'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'invited_by' => auth()->id(),
            'invited_at' => now(),
        ]);

        $user->assignRole($request->role);

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('user.edit');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|exists:roles,name'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return response()->json($user->load('roles'));
    }

    public function destroy(User $user)
    {
        $this->authorize('user.delete');

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    public function resendInvitation(User $user)
    {
        $this->authorize('user.edit');

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'User has already verified their email.'
            ], 422);
        }

        $tempPassword = Str::random(12);
        $user->update([
            'password' => Hash::make($tempPassword),
            'invitation_sent_at' => now(),
        ]);

        $user->notify(new UserInvitation($tempPassword, $user->getRoleNames()->first()));

        return response()->json([
            'message' => 'Invitation resent successfully.'
        ]);
    }

    public function getInvitationStats()
    {
        $this->authorize('user.view');

        $stats = [
            'total_users' => User::count(),
            'pending_invitations' => User::whereNull('email_verified_at')->count(),
            'recent_invitations' => User::where('invited_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json($stats);
    }
}