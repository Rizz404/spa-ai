<?php

namespace App\Http\Requests\Api;

use App\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * One guest message plus where in the UI the guest is when they send it.
 */
class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'guest_token' => ['required', 'uuid'],
            'message' => ['required', 'string', 'max:2000'],
            'scene' => ['nullable', Rule::in(Conversation::SCENES)],
            'selected_treatment' => ['nullable', 'string', 'max:120'],
            'selected_treatment_room' => ['nullable', 'string', 'max:120'],
            'selected_facility' => ['nullable', 'integer', 'min:1'],
            'reservation' => ['nullable', 'array'],
            'reservation.reservation_date' => ['nullable', 'date_format:Y-m-d'],
            'reservation.time_slot' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'reservation.guests' => ['nullable', 'integer', 'min:1', 'max:2'],
            'reservation.occasion' => ['nullable', 'string', 'max:2'],
            'reservation.treatment_slug' => ['nullable', 'string', 'max:120'],
            'reservation.treatment_room_slug' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * Where the guest is in the UI, as the concierge's conversation remembers it.
     *
     * @return array{scene?: ?string, selected_treatment?: ?string, selected_treatment_room?: ?string, selected_facility?: ?int, reservation?: array<string, mixed>}
     */
    public function uiContext(): array
    {
        $data = $this->validated();

        return [
            'scene' => $data['scene'] ?? null,
            'selected_treatment' => $data['selected_treatment'] ?? ($data['reservation']['treatment_slug'] ?? null),
            'selected_facility' => $data['selected_facility'] ?? null,
            'selected_treatment_room' => $data['selected_treatment_room'] ?? ($data['reservation']['treatment_room_slug'] ?? null),
            ...array_key_exists('reservation', $data)
                ? ['reservation' => array_filter($data['reservation'] ?? [], fn ($value) => $value !== null && $value !== '')]
                : [],
        ];
    }
}
