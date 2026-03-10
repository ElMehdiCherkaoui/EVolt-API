<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Station;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Station::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'connector_type' => 'required|string',
            'power_kw' => 'required|numeric',
            'status' => 'required|in:available,occupied,maintenance'
        ]);

        $station = Station::create($validatedData);
        return response()->json([
            'message' => 'Station Created Successfuly',
            'station' => $station
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $station = Station::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'connector_type' => 'required|string',
            'power_kw' => 'required|numeric',
            'status' => 'required|in:available,occupied,maintenance'
        ]);

        $station->update($validatedData);

        return response()->json([
            'message' => 'Station updated',
            'station' => $station
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $station = Station::findOrFail($id);
        $station->delete();

        return response()->json([
            'message' => 'Station deleted'
        ], 200);
    }
}
