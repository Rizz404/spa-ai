<?php

namespace App\Modules\Conversation\Actions;

use App\Models\Conversation;
use App\Models\Spa;

/**
 * Remembers where in the UI the guest is, so the concierge can answer for "this
 * treatment" or the reservation they are filling in. Only values that exist for
 * this spa are kept; nothing personal is stored here.
 */
class RememberUiContext
{
    /**
     * @param  array{scene?: ?string, selected_treatment?: ?string, selected_treatment_room?: ?string, selected_facility?: ?int, reservation?: ?array<string, mixed>}  $context
     */
    public function handle(Spa $spa, Conversation $conversation, array $context): void
    {
        $updates = [];

        if (in_array($context['scene'] ?? null, Conversation::SCENES, true)) {
            $updates['current_scene'] = $context['scene'];
        }

        if (filled($context['selected_treatment'] ?? null)) {
            $treatment = $spa->treatments()->where('is_active', true)->where('slug', $context['selected_treatment'])->first();

            if ($treatment) {
                $updates['selected_treatment_id'] = $treatment->id;
            }
        }

        if (filled($context['selected_treatment_room'] ?? null)) {
            $area = $spa->treatmentRooms()->where('is_active', true)->where('slug', $context['selected_treatment_room'])->first();

            if ($area) {
                $updates['selected_treatment_room_id'] = $area->id;
            }
        }

        if (filled($context['selected_facility'] ?? null)) {
            $facility = $spa->knowledgeItems()->where('is_active', true)->whereKey($context['selected_facility'])->first();

            if ($facility) {
                $updates['selected_facility_id'] = $facility->id;
            }
        }

        if (array_key_exists('reservation', $context)) {
            $updates['reservation_state'] = $context['reservation'] ?: null;
        }

        if ($updates !== []) {
            $conversation->update($updates);
        }
    }
}
