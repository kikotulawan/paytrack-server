<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // List users with their info, roles, and permissions (with pagination and search)
    public function index(Request $request)
    {
        $query = User::with(['info', 'roles', 'permissions']);

        if ($search = $request->input('search')) {
            $query->whereHas('info', function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('abbreviation', 'like', "%{$search}%");
            })->orWhere('email', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 20);

        $result = $query->paginate($perPage);

        activity()
            ->causedBy(auth()->user()->info)
            ->log('Viewed user list');

        return response()->json($result);
    }

    // Store a new user with user info and assign role
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'info.firstname' => 'required|string',
            'info.lastname' => 'required|string',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $userInfoData = $request->input('info', []);
        $userInfoData['user_id'] = $user->id;
        UserInfo::create($userInfoData);

        // Assign only one role to user
        $user->syncRoles([$request->role]);

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($user)
            ->log('Created user ' . ($user->info->full_name ?? $user->email));

        return response()->json($user->load(['info', 'roles']), 201);
    }

    // Show a specific user with info, roles, and permissions
    public function show($id)
    {
        $user = User::with(['info', 'roles', 'permissions'])->findOrFail($id);

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($user)
            ->log('Viewed user ' . ($user->info->full_name ?? $user->email));

        return response()->json($user);
    }

    // Update a user and their info, including role
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|min:6',
            'info.firstname' => 'sometimes|required|string',
            'info.lastname' => 'sometimes|required|string',
            'role' => 'sometimes|required|exists:roles,name',
        ]);

        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        if ($request->has('info')) {
            $user->info()->updateOrCreate(
                ['user_id' => $user->id],
                $request->input('info')
            );
        }

        // Ensure user has only one role if provided
        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($user)
            ->log('Updated user ' . ($user->info->full_name ?? $user->email));

        return response()->json($user->load(['info', 'roles']));
    }

    // Delete a user and their info
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->info()->delete();
        $user->delete();

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($user)
            ->log('Deleted user ' . ($user->info->full_name ?? $user->email));

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Deactivate a user
    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = false;
        $user->save();

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($user)
            ->log('Deactivated user ' . ($user->info->full_name ?? $user->email));

        return response()->json(['message' => 'User deactivated successfully']);
    }

    // Activate a user
    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = true;
        $user->save();

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($user)
            ->log('Activated user ' . ($user->info->full_name ?? $user->email));

        return response()->json(['message' => 'User activated successfully']);
    }
}