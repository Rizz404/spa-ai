<?php

namespace App\Http\Controllers;

use App\Http\Middleware\ResolveSpa;
use App\Models\Spa;
use App\Models\SpaKnowledgeItem;
use App\Models\Treatment;
use App\Models\TreatmentRoom;
use App\Modules\Experience\Presenters\StageBackdrop;
use App\Modules\Experience\Presenters\StageCopy;
use App\Modules\Experience\Queries\BuildStage;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The guest-facing stage: one page per scene, all built from the same shell
 * data. The spa and the language are resolved by the ResolveSpa middleware.
 */
class SpaPageController extends Controller
{
    public function __construct(private readonly BuildStage $stage) {}

    public function index(Request $request): View
    {
        $spas = Spa::published()->orderBy('name')->get();

        $locale = in_array($request->query('lang'), config('spa.locales'), true)
            ? $request->query('lang')
            : ($spas->first()?->default_locale ?? config('spa.default_locale'));

        return view('opening', [
            'spas' => $spas,
            'locale' => $locale,
            'supportedLocales' => config('spa.locales'),
            'opening' => StageCopy::opening($locale),
            'avatarImage' => StageBackdrop::avatar(),
            'backdrop' => StageBackdrop::for('home'),
        ]);
    }

    public function show(Request $request): View
    {
        return view('spa.show', $this->stageData($request, 'home'));
    }

    public function treatments(Request $request): View
    {
        return view('spa.treatments-index', $this->stageData($request, 'treatments'));
    }

    public function treatment(Request $request, string $spaSlug, string $itemSlug): View
    {
        $data = $this->stageData($request, 'treatment');
        $items = $data['treatments'];
        $index = $items->search(fn (Treatment $item) => $item->slug === $itemSlug);

        abort_if($index === false, 404);

        return view('spa.treatments-detail', [
            ...$data,
            'treatment' => $items[$index],
            'itemIndex' => $index,
            ...$this->neighbours($items, $index, 'Item'),
        ]);
    }

    public function rooms(Request $request): View
    {
        return view('spa.rooms-index', $this->stageData($request, 'rooms'));
    }

    public function treatmentRoom(Request $request, string $spaSlug, string $areaSlug): View
    {
        $data = $this->stageData($request, 'treatment-room');
        $areas = $data['treatmentRooms'];
        $index = $areas->search(fn (TreatmentRoom $area) => $area->slug === $areaSlug);

        abort_if($index === false, 404);

        return view('spa.rooms-detail', [
            ...$data,
            'treatmentRoom' => $areas[$index],
            'areaIndex' => $index,
            ...$this->neighbours($areas, $index, 'Area'),
        ]);
    }

    public function facilities(Request $request): View
    {
        return view('spa.facilities-index', $this->stageData($request, 'facilities'));
    }

    public function facility(Request $request, string $spaSlug, int $facilityId): View
    {
        $data = $this->stageData($request, 'facility');
        $facilities = $data['facilities'];
        $index = $facilities->search(fn (SpaKnowledgeItem $item) => $item->id === $facilityId);

        abort_if($index === false, 404);

        return view('spa.facility-detail', [
            ...$data,
            'facility' => $facilities[$index],
            'facilityIndex' => $index,
            ...$this->neighbours($facilities, $index, 'Facility'),
        ]);
    }

    public function info(Request $request): View
    {
        return view('spa.info-index', $this->stageData($request, 'info'));
    }

    public function staff(Request $request): View
    {
        return view('spa.staff-index', $this->stageData($request, 'staff'));
    }

    public function reservationScene(Request $request): View
    {
        return view('spa.reservation-index', [
            ...$this->stageData($request, 'reservation'),
            'preselectedArea' => (string) $request->query('area', ''),
            'preselectedTreatment' => (string) $request->query('treatment', ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function stageData(Request $request, string $scene): array
    {
        return $this->stage->handle(ResolveSpa::spa($request), ResolveSpa::locale($request), $scene);
    }

    /**
     * The previous and next entry around an index, wrapping at both ends.
     *
     * @param  Collection<int, mixed>  $items
     * @return array<string, mixed>
     */
    private function neighbours(Collection $items, int $index, string $suffix): array
    {
        return [
            'previous'.$suffix => $items[($index - 1 + $items->count()) % $items->count()],
            'next'.$suffix => $items[($index + 1) % $items->count()],
        ];
    }
}
