<?php

namespace App\Http\Controllers;

use App\Models\NatureOfCollection;
use Illuminate\Http\Request;

class NatureOfCollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(NatureOfCollection::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'parent' => 'required|string|max:255',
            'lbp_bank_account_number' => 'required|string|max:255',
        ]);

        $natureOfCollection = NatureOfCollection::create($request->all());

        return response()->json($natureOfCollection, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $natureOfCollection = NatureOfCollection::findOrFail($id);

        return response()->json($natureOfCollection);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NatureOfCollection $natureOfCollection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $natureOfCollection = NatureOfCollection::findOrFail($id);

        $request->validate([
            'type' => 'sometimes|required|string|max:255',
            'parent' => 'sometimes|required|string|max:255',
            'lbp_bank_account_number' => 'sometimes|required|string|max:255',
        ]);

        $natureOfCollection->update($request->all());

        return response()->json($natureOfCollection);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $natureOfCollection = NatureOfCollection::findOrFail($id);
        $natureOfCollection->delete();

        return response()->json(['message' => 'Nature of Collection deleted successfully']);
    }
}