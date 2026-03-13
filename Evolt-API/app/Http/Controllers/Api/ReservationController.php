<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateStationAvailabilityJob;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Reservation::with(['user', 'station'])->get(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'start_time' => 'required|date',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'status' => 'required|string|max:255',
        ]);

        $startTime = Carbon::parse($validate['start_time']);
        $endTime = (clone $startTime)->addMinutes((int) $validate['estimated_duration_minutes']);

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'station_id' => $validate['station_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'estimated_duration_minutes' => $validate['estimated_duration_minutes'],
            'status' => $validate['status'],
        ]);

        UpdateStationAvailabilityJob::dispatch($reservation->id)->delay($reservation->end_time);

        return response()->json([
            'message' => 'Reservation Created Successfuly',
            'reservation' => $reservation
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reservation = Reservation::with(['user', 'station'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($reservation, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);

        $validate = $request->validate([
            'start_time' => 'sometimes|date',
            'estimated_duration_minutes' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string|max:255',
        ]);

        $startTime = array_key_exists('start_time', $validate)
            ? Carbon::parse($validate['start_time'])
            : Carbon::parse($reservation->start_time);

        $duration = array_key_exists('estimated_duration_minutes', $validate)
            ? (int) $validate['estimated_duration_minutes']
            : (int) $reservation->estimated_duration_minutes;

        $updateData = [
            'start_time' => $startTime,
            'estimated_duration_minutes' => $duration,
            'end_time' => (clone $startTime)->addMinutes($duration),
        ];

        if (array_key_exists('status', $validate)) {
            $updateData['status'] = $validate['status'];
        }

        $reservation->update($updateData);

        UpdateStationAvailabilityJob::dispatch($reservation->id)->delay($reservation->end_time);

        return response()->json([
            'message' => 'Reservation updated',
            'reservation' => $reservation
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);
        $reservation->delete();

        return response()->json([
            'message' => 'Reservation deleted'
        ], 200);
    }


    public function cancel(string $id)
    {
        $reservation = Reservation::where('user_id', Auth::id())->findOrFail($id);

        if ($reservation->status === 'cancelled') {
            return response()->json(['message' => 'Reservation already cancelled'], 400);
        }

        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Reservation cancelled',
            'reservation' => $reservation
        ], 200);
    }

    public function myHistory()
    {
        $reservations = Reservation::with('station')
            ->where('user_id', Auth::id())
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($reservations, 200);
    }
}
