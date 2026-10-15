<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveSpa;
use App\Http\Requests\Api\QuoteReservationRequest;
use App\Http\Requests\Api\StoreReservationRequest;
use App\Models\Conversation;
use App\Models\Spa;
use App\Models\TreatmentRoom;
use App\Modules\Reservation\Actions\CreateReservationRequest;
use App\Modules\Reservation\Actions\ResolveReservationTarget;
use App\Modules\Reservation\Queries\FindAlternatives;
use App\Modules\Reservation\Support\ReservationHandover;
use App\Modules\Reservation\Support\RoomAvailability;
use App\Modules\Reservation\Validation\ReservationRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function __construct(
        private readonly RoomAvailability $availability,
        private readonly ResolveReservationTarget $resolveTarget,
        private readonly FindAlternatives $findAlternatives,
    ) {}

    public function quote(QuoteReservationRequest $request): JsonResponse
    {
        $spa = ResolveSpa::spa($request);
        $locale = ResolveSpa::locale($request);

        $data = $request->validated();
        [$area, $date, $treatment] = $this->resolveTarget->handle($spa, $data, $locale);

        $quote = $this->availability->quote($area, $date, $data['time_slot']);

        if (! $quote) {
            return $this->unavailable($spa, $area, $date, $data, $locale);
        }

        return response()->json([
            'available' => true,
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_rooms' => $quote['available_rooms'],
            'fee' => $quote['fee'],
            'treatment_name' => $treatment->translatedName($locale),
            'duration_minutes' => $treatment->duration_minutes,
            'treatment_price' => (float) $treatment->price,
            'total' => $quote['fee'] + (float) $treatment->price * (int) $data['guests'],
            'minimum_spend' => $quote['minimum_spend'],
            'currency' => $spa->currency,
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        CreateReservationRequest $create,
        ReservationHandover $handover,
    ): JsonResponse {
        $spa = ResolveSpa::spa($request);
        $locale = ResolveSpa::locale($request);

        $data = $request->validated();
        $isEmail = $request->isEmailContact();

        [$area, $date] = $this->resolveTarget->handle($spa, $data, $locale);

        $conversation = isset($data['guest_token'])
            ? Conversation::where('spa_id', $spa->id)->where('guest_token', $data['guest_token'])->first()
            : null;

        $reservation = $create->handle($spa, $area, [
            'treatment_slug' => $data['treatment_slug'],
            'reservation_date' => $date,
            'time_slot' => $data['time_slot'],
            'guests' => (int) $data['guests'],
            'occasion' => $data['occasion'] ?? null,
            'guest_name' => $data['guest_name'],
            'guest_email' => $isEmail ? $data['contact_value'] : null,
            'guest_phone' => $isEmail ? null : $data['contact_value'],
            'contact_type' => $data['contact_type'],
            'notes' => $data['special_request'] ?? null,
        ], $conversation);

        if (! $reservation) {
            return $this->unavailable($spa, $area, $date, $data, $locale);
        }

        return response()->json([
            'reference' => $reservation->reference,
            'status' => $reservation->status,
            'fee' => (float) $reservation->quoted_total,
            'currency' => $spa->currency,
            'handover' => $handover->forReservation($spa, $reservation, $locale),
        ], 201);
    }

    /**
     * No table is free in that area and slot: say so, and offer what is.
     *
     * @param  array<string, mixed>  $data
     */
    private function unavailable(Spa $spa, TreatmentRoom $requested, CarbonImmutable $date, array $data, string $locale): JsonResponse
    {
        $message = ReservationRules::message('unavailable', $locale);

        return response()->json([
            'message' => $message,
            'errors' => ['treatment_room_slug' => [$message]],
            'alternatives' => $this->findAlternatives->handle($spa, $requested, $date, $data['time_slot'], (int) $data['guests'], $locale),
            'currency' => $spa->currency,
        ], 422);
    }
}
