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
        $isAdmin = false;

        // 1. Check if it's an Admin
        $admin = \App\Models\Admin::where('email', $identifier)->orWhere('username', $identifier)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            $user = $admin;
            $isAdmin = true;
        }

        // 2. If not admin, check if it's a User (Vendor)
        if (!$user) {
            $vendorUser = User::where('email', $identifier)->first();
            
            // Check by account_id if email not found
            if (!$vendorUser) {
                $vendor = \App\Models\Vendor::where('account_id', $identifier)->first();
                if ($vendor) {
                    $vendorUser = $vendor->user;
                }
            }

            if ($vendorUser && Hash::check($request->password, $vendorUser->password)) {
                $user = $vendorUser;
            }
        }

        // Output error if neither matched
        if (!$user) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Create token (Sanctum handles morphs automatically based on the model instance)
        $tokenName = $isAdmin ? 'admin-token' : 'auth-token';
        $token = $user->createToken($tokenName)->plainTextToken;

        $vendorType = null;
        if ($user->role === 'vendor') {
            $vendorRecord = \App\Models\Vendor::where('user_id', $user->id)->first();
            if ($vendorRecord) {
                $vendorType = $vendorRecord->vendor_type ?? null;
            }
        }

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'vendor_type' => $vendorType,
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

        $vendorType = null;
        if ($user->role === 'vendor') {
            $vendorRecord = \App\Models\Vendor::where('user_id', $user->id)->first();
            if ($vendorRecord) {
                $vendorType = $vendorRecord->vendor_type ?? null;
            }
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'profile_image' => $user->profile_image,
                'bio' => $user->bio,
                'vendor_type' => $vendorType,
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
        $tableName = $user->getTable(); // either 'admins' or 'users'

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:' . $tableName . ',username,' . $user->id . ',_id',
            'email' => 'sometimes|email|unique:' . $tableName . ',email,' . $user->id . ',_id',
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
