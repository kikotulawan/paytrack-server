<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controller; 

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // List all roles with optional pagination and search
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 20); // Default 15 items per page

        return response()->json($query->paginate($perPage));
    }

    // Store a new role
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        activity()
            ->causedBy(auth()->user()->info)
            ->log('Created role '.$role->name);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'), 201);
    }

    // Show a specific role
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    // Update a role
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->name = $request->name;

        activity()
            ->causedBy(auth()->user()->info)
            ->log('Updated role '.$role->name.' to '.$request->name);

            
        $role->save();

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'));
    }

    // Delete a role
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete role: one or more users are assigned to this role.'
            ], 409);
        }

        activity()
            ->causedBy(auth()->user()->info)
            ->log('Deleted role '.$role->name);

        $role->delete();
        

        return response()->json(['message' => 'Role deleted successfully']);
    }
}