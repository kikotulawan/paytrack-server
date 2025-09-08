<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DesignationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // List all designations with users (pagination & search)
    public function index(Request $request)
    {
        $query = Designation::with('users');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 20);

        return response()->json($query->paginate($perPage));
    }

    // Store a new designation
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:designations,name',
        ]);

        $designation = Designation::create($request->only('name'));

        return response()->json($designation, 201);
    }

    // Show a specific designation with users
    public function show($id)
    {
        $designation = Designation::with('users')->findOrFail($id);
        return response()->json($designation);
    }

    // Update a designation
    public function update(Request $request, $id)
    {
        $designation = Designation::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:designations,name,' . $designation->id,
        ]);

        $designation->update($request->only('name'));

        return response()->json($designation);
    }

    // Delete a designation
    public function destroy($id)
    {
        $designation = Designation::findOrFail($id);
        $designation->delete();

        return response()->json(['message' => 'Designation deleted successfully']);
    }

    // Assign a user to a designation
    public function assignUser(Request $request, $designationId)
    {
        $designation = Designation::findOrFail($designationId);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->designation_id = $designation->id;
        $user->save();

        return response()->json(['message' => 'User assigned to designation successfully']);
    }
}