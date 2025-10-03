<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\URL;

class RegistrationController extends Controller
{
    public function complete(Request $request)
    {
        if (!URL::hasValidSignature($request)) {
            return response()->json([
                'message' => 'Invalid or expired registration link.'
            ], 422);
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Registration already completed.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        // Log in the user automatically
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles', 'permissions'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Registration completed successfully!'
        ]);
    }

    public function validateInvitation(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->email_verified_at) {
            return response()->json([
                'valid' => false,
                'message' => 'Registration already completed.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }
}