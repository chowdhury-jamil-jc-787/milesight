<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Value;

class ValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $values = Value::all();
        return response()->json(['data' => $values], 200);
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
            'value' => 'required|boolean',
        ]);

        $value = Value::create($request->only('value'));

        return response()->json([
            'message' => 'Value created successfully!',
            'data' => $value
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Value $value)
    {
        return response()->json(['data' => $value], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Value $value)
    {
        $request->validate([
            'value' => 'required|boolean',
        ]);

        $value->update($request->only('value'));

        return response()->json([
            'message' => 'Value updated successfully!',
            'data' => $value
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Value $value)
    {
        $value->delete();

        return response()->json([
            'message' => 'Value deleted successfully!'
        ], 200);
    }
}
