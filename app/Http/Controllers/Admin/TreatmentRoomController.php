<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentSpa;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TreatmentRoomRequest;
use App\Models\TreatmentRoom;
use App\Modules\Rooms\Actions\SaveTreatmentRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TreatmentRoomController extends Controller
{
    use ResolvesCurrentSpa;

    public function index(Request $request): View
    {
        $spa = $this->currentSpa($request);

        $treatmentRooms = $spa->treatmentRooms()->withCount('images')->orderBy('sort_order')->get();

        return view('admin.treatment-rooms.index', compact('spa', 'treatmentRooms'));
    }

    public function create(Request $request): View
    {
        $spa = $this->currentSpa($request);

        return view('admin.treatment-rooms.form', [
            'spa' => $spa,
            'treatmentRoom' => new TreatmentRoom(['room_type' => TreatmentRoom::TYPE_SINGLE, 'min_guests' => 1, 'max_guests' => 2, 'reservation_fee' => 0, 'is_active' => true]),
        ]);
    }

    public function store(TreatmentRoomRequest $request, SaveTreatmentRoom $save): RedirectResponse
    {
        $treatmentRoom = $save->handle(
            $this->currentSpa($request),
            $request->treatmentRoomAttributes(),
            $request->imageIdsToDelete(),
            $request->newImages(),
        );

        return redirect()->route('admin.treatment-rooms.index')->with('status', "Rooms area \"{$treatmentRoom->name}\" created.");
    }

    public function edit(Request $request, TreatmentRoom $treatmentRoom): View
    {
        $spa = $this->currentSpa($request);
        Gate::authorize('update', $treatmentRoom);

        $treatmentRoom->load('images');

        return view('admin.treatment-rooms.form', compact('spa', 'treatmentRoom'));
    }

    public function update(TreatmentRoomRequest $request, TreatmentRoom $treatmentRoom, SaveTreatmentRoom $save): RedirectResponse
    {
        $save->handle($this->currentSpa($request), $request->treatmentRoomAttributes(), $request->imageIdsToDelete(), $request->newImages(), $treatmentRoom);

        return redirect()->route('admin.treatment-rooms.index')->with('status', "Rooms area \"{$treatmentRoom->name}\" updated.");
    }

    public function destroy(Request $request, TreatmentRoom $treatmentRoom): RedirectResponse
    {
        $this->currentSpa($request);
        Gate::authorize('delete', $treatmentRoom);

        $treatmentRoom->delete();

        return redirect()->route('admin.treatment-rooms.index')->with('status', 'Rooms area deleted.');
    }
}
