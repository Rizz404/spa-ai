<?php

namespace App\Modules\Experience\Queries;

use App\Models\Spa;
use App\Models\SpaKnowledgeItem;
use App\Modules\Experience\Presenters\SceneNarrations;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Presenters\StageNavigation;
use App\Modules\Reservation\Support\ReservationHandover;
use App\Modules\Reservation\Support\RoomAvailability;

/**
 * Everything the shared stage shell needs, for whichever scene the guest has
 * walked into.
 */
class BuildStage
{
    public function __construct(
        private readonly ReservationHandover $handover,
        private readonly RoomAvailability $availability,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Spa $spa, string $locale, string $scene): array
    {
        $treatments = $spa->treatments()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->values();

        $treatmentRooms = $spa->treatmentRooms()
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->values();

        $facilities = $spa->knowledgeItems()->where('is_active', true)
            ->whereIn('category', [SpaKnowledgeItem::CATEGORY_FACILITIES, SpaKnowledgeItem::CATEGORY_EVENTS, SpaKnowledgeItem::CATEGORY_LOCATION])
            ->orderBy('sort_order')->get()->values();

        $labels = StageCopy::labels($locale);

        return [
            'spa' => $spa,
            'treatments' => $treatments,
            'treatmentRooms' => $treatmentRooms,
            'locale' => $locale,
            'scene' => $scene,
            'backdrop' => StageBackdrop::for($scene),
            'navItems' => StageNavigation::for($spa, $locale, $labels),
            'supportedLocales' => config('spa.locales'),
            'labels' => $labels,
            'stageCopy' => StageCopy::copy($locale),
            'terms' => StageCopy::terms($locale),
            'wizard' => StageCopy::wizard($locale),
            'narration' => StageCopy::narration($locale),
            'treatmentsNarrations' => SceneNarrations::treatments($spa, $treatments, $locale),
            'roomsNarrations' => SceneNarrations::rooms($spa, $treatmentRooms, $locale),
            'staffLinks' => $this->handover->forStaff($spa, $locale),
            'today' => now($spa->timezone)->toDateString(),
            'maxAdvanceDays' => RoomAvailability::MAX_ADVANCE_DAYS,
            'slots' => collect($this->availability->slotsFor($spa))
                ->map(fn (string $slot) => ['value' => $slot, 'label' => RoomAvailability::slotRange($slot)])
                ->all(),
            'infoItems' => $spa->knowledgeItems()->where('is_active', true)
                ->whereIn('category', [SpaKnowledgeItem::CATEGORY_GENERAL, SpaKnowledgeItem::CATEGORY_POLICIES, SpaKnowledgeItem::CATEGORY_FAQ])
                ->orderBy('sort_order')->get()->groupBy('category'),
            'facilities' => $facilities,
            'sceneNarrations' => SceneNarrations::scenes($spa, $facilities, $locale),
        ];
    }
}
