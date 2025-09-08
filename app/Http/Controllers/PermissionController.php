<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controller;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // List all permissions with optional pagination and search
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // Store a new permission
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        activity()
            ->causedBy(auth()->user()->info)
            ->log('Created permission '.$permission->name);

        return response()->json($permission, 201);
    }

    // Show a specific permission
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    // Update a permission
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        $oldName = $permission->name;
        $permission->name = $request->name;
        $permission->save();

        activity()
            ->causedBy(auth()->user()->info)
            ->log("Updated permission {$oldName} to {$permission->name}");

        return response()->json($permission);
    }

    // Delete a permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        activity()
            ->causedBy(auth()->user()->info)
            ->log('Deleted permission '.$permission->name);

        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}