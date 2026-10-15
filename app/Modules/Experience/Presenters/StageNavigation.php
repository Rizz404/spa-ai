<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Spa;

/**
 * The main treatments of the stage. Sections that live on another page are marked
 * so the stage can fade out, as if the concierge were walking the guest over.
 */
final class StageNavigation
{
    /**
     * @param  array<string, string>  $labels
     * @return list<array{key: string, icon: string, label: string, chip: string, href: string, exit: bool, tour: ?string, topic: string}>
     */
    public static function for(Spa $spa, string $locale, array $labels): array
    {
        $tour = StageCopy::narration($locale);
        $url = fn (string $route) => route($route, ['spaSlug' => $spa->slug, 'lang' => $locale]);

        return [
            ['key' => 'treatments', 'icon' => 'leaf', 'label' => $labels['treatments_heading'], 'chip' => $labels['chip_treatments'], 'href' => $url('spa.treatments'), 'exit' => true, 'tour' => $tour['tour_treatments'], 'topic' => $labels['nav_treatments_q']],
            ['key' => 'rooms', 'icon' => 'bed', 'label' => $labels['rooms_heading'], 'chip' => $labels['chip_rooms'], 'href' => $url('spa.rooms'), 'exit' => true, 'tour' => $tour['tour_rooms'], 'topic' => $labels['nav_rooms_q']],
            ['key' => 'facilities', 'icon' => 'sparkle', 'label' => $labels['facilities_heading'], 'chip' => $labels['chip_facilities'], 'href' => $url('spa.facilities'), 'exit' => true, 'tour' => $tour['tour_facilities'], 'topic' => $labels['nav_facilities_q']],
            ['key' => 'info', 'icon' => 'info', 'label' => $labels['info_heading'], 'chip' => $labels['chip_info'], 'href' => $url('spa.info'), 'exit' => true, 'tour' => $tour['tour_info'], 'topic' => $labels['nav_info_q']],
            ['key' => 'reservation', 'icon' => 'calendar', 'label' => $labels['reservation_heading'], 'chip' => $labels['chip_reservation'], 'href' => $url('spa.reservation'), 'exit' => true, 'tour' => $tour['tour_reservation'], 'topic' => $labels['nav_reservation_q']],
            ['key' => 'staff', 'icon' => 'chat', 'label' => $labels['staff_heading'], 'chip' => $labels['chip_staff'], 'href' => $url('spa.staff'), 'exit' => true, 'tour' => $tour['tour_staff'], 'topic' => $labels['nav_staff_q']],
        ];
    }
}
