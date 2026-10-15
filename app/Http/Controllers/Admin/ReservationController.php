<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentSpa;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReservationStatusRequest;
use App\Models\Reservation;
use App\Modules\Reservation\Actions\ChangeReservationStatus;
use App\Modules\Reservation\Enums\ReservationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    use ResolvesCurrentSpa;

    public function index(Request $request): View
    {
        $spa = $this->currentSpa($request);

        $status = in_array($request->query('status'), ReservationStatus::values(), true)
            ? $request->query('status')
            : null;

        $reservations = $spa->reservations()
            ->with('treatmentRoom')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reservations.index', compact('spa', 'reservations', 'status'));
    }

    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation, ChangeReservationStatus $changeStatus): RedirectResponse
    {
        $this->currentSpa($request);

        $status = $request->validated('status');

        $changeStatus->handle($reservation, $status);

        return back()->with('status', "Reservation {$reservation->reference} marked as {$status}.");
    }
}
