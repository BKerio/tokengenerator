<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required',
            'password' => 'required',
        ]);

        $identifier = $request->identifier;
        $user = null;

        // 1. Try to find user by email
        $user = User::where('email', $identifier)->first();

        // 2. If not found by email, check if it's a vendor account_id
        if (!$user) {
            $vendor = \App\Models\Vendor::where('account_id', $identifier)->first();
            if ($vendor) {
                $user = $vendor->user;
            }
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // 3. Security Check: Admin can ONLY login via email
        // If they logged in via account_id but are an admin, we might want to block it, 
        // but currently account_id is only in the vendors collection.
        // However, if we ever share identifiers, we should be explicit:
        // if ($user->role === 'admin' && !filter_var($identifier, FILTER_VALIDATE_EMAIL)) { ... }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'status' => 200 // Added for compatibility with user's frontend code
        ]);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'profile_image' => $user->profile_image,
                'bio' => $user->bio,
            ]
        ]);
    }

    /**
     * Get account details
     */
    public function getAccount(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'role' => $user->role,
            'profile_image' => $user->profile_image,
            'bio' => $user->bio,
        ]);
    }

    /**
     * Update account details
     */
    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id . ',_id',
            'email' => 'sometimes|email|unique:users,email,' . $user->id . ',_id',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Account updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
}
