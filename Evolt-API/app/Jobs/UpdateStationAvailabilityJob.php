<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Station;

class UpdateStationAvailabilityJob implements ShouldQueue
{
	use Queueable;

	public int $reservationId;

	/**
	 * Create a new job instance.
	 */
	public function __construct(int $reservationId)
	{
		$this->reservationId = $reservationId;
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		$reservation = Reservation::findOrFail($this->reservationId);

		if ($reservation->status === 'cancelled') {
			return;
		}

		if (now()->lt($reservation->end_time)) {
			return;
		}

		$hasAnotherActiveReservation = Reservation::where('station_id', $reservation->station_id)
			->where('id', '!=', $reservation->id)
			->where('status', '!=', 'cancelled')
			->where('start_time', '<=', now())
			->where('end_time', '>=', now())
			->exists();

		if ($hasAnotherActiveReservation) {
			return;
		}

		$station = Station::findOrFail($reservation->station_id);

		$station->update([
			'status' => 'available',
		]);
	}
}
