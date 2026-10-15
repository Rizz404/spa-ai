<?php

namespace App\Modules\Assistant\Tools\Support;

use App\Models\Spa;
use App\Models\Treatment;
use App\Models\TreatmentRoom;

/**
 * The compact facts about a treatments item or treatment room that both the model and
 * the guest's cards are given.
 */
final class CatalogSummaries
{
    /**
     * @return array<string, mixed>
     */
    public static function treatment(Treatment $item, Spa $spa, string $locale): array
    {
        return [
            'treatment_slug' => $item->slug,
            'name' => $item->translatedName($locale),
            'category' => $item->category,
            'price' => (float) $item->price,
            'currency' => $spa->currency,
            'duration_label' => $item->duration_label,
            'duration_minutes' => $item->duration_minutes,
            'tags' => $item->tags ?? [],
            'contraindications' => $item->contraindications ?? [],
            'is_unavailable' => $item->is_unavailable,
            'image_url' => $item->image_source,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function treatmentRoom(TreatmentRoom $area, Spa $spa, string $locale): array
    {
        return [
            'name' => $area->translatedName($locale),
            'room_type' => $area->room_type,
            'min_guests' => $area->min_guests,
            'max_guests' => $area->max_guests,
            'features' => $area->features ?? [],
            'reservation_fee' => (float) $area->reservation_fee,
            'minimum_spend' => $area->minimum_spend !== null ? (float) $area->minimum_spend : null,
            'currency' => $spa->currency,
            'thumbnail_url' => $area->images->first()?->image_source,
        ];
    }

    public static function treatmentBySlug(Spa $spa, string $slug): ?Treatment
    {
        return $spa->treatments()->where('is_active', true)->where('slug', $slug)->first();
    }

    public static function treatmentRoomBySlug(Spa $spa, string $slug): ?TreatmentRoom
    {
        return $spa->treatmentRooms()->where('is_active', true)->where('slug', $slug)->first();
    }
}
