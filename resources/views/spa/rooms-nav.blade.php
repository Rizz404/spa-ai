{{-- On the rooms scenes the side card becomes the area index, so the guest can step between areas without going back. --}}
<aside class="stage-treatments" aria-label="{{ $labels['rooms_heading'] }}">
    <a href="{{ route('spa.show', ['spaSlug' => $spa->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-treatments-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['rooms_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($treatmentRooms as $item)
            <a href="{{ route('spa.treatment-room', ['spaSlug' => $spa->slug, 'areaSlug' => $item->slug, 'lang' => $locale]) }}"
                data-stage-exit
                @if (($treatmentRoom ?? null)?->slug === $item->slug) aria-current="page" @endif>
                @if ($item->images->first())
                    <img class="item-nav-thumb" src="{{ $item->images->first()->image_source }}" alt="" loading="lazy">
                @endif
                <span class="item-nav-text">
                    <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                    <span class="item-nav-price">{{ $terms['room_type'][$item->room_type] ?? Str::headline($item->room_type) }} · {{ $item->min_guests > 1 ? $item->min_guests.'–' : '' }}{{ $item->max_guests }} {{ $stageCopy['guests_unit'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>
    <span class="stage-treatments-footer">{{ $stageCopy['available'] }} · SPA AI</span>
</aside>
