<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        // Attempt to find the user first
        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user || !$user->is_active) {
            return response()->json(['error' => 'Account is inactive or does not exist'], 401);
        }

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        activity()
            ->causedBy(auth()->user()->info)
            ->log('Logged in');

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        return response()->json([
            'user' => $user->load('info'),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'), 
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        
        $user = auth()->user();

        activity()
            ->causedBy(auth()->user()->info)
            ->log('Logged out');
            
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }

    /**
     * Change the authenticated user's password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword()
    {
        $user = auth()->user();

        $data = request()->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        // Check current password
        if (!\Hash::check($data['current_password'], $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 422);
        }

        // Update password
        $user->password = bcrypt($data['new_password']);
        $user->save();

        activity()
            ->causedBy($user->info)
            ->log('Changed password');

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * Update another user's password (Superadmin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserPassword(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $admin = auth()->user();
        if (!$admin->hasRole('superadmin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $user = \App\Models\User::findOrFail($request->user_id);
        $user->password = bcrypt($request->new_password);
        $user->save();

        activity()
            ->causedBy($admin->info)
            ->performedOn($user)
            ->log('Superadmin changed password for user ' . ($user->info->full_name ?? $user->email));

        return response()->json(['message' => 'User password updated successfully']);
    }
}