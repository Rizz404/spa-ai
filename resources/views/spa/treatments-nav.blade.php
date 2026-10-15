{{-- On the treatments scenes the side card becomes the treatments index, so the guest can step between treatments without going back. --}}
<aside class="stage-treatments" aria-label="{{ $labels['treatments_heading'] }}">
    <a href="{{ route('spa.show', ['spaSlug' => $spa->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" class="stage-treatments-back"><span aria-hidden="true">‹</span> {{ $stageCopy['explore'] }}</a>
    <p class="stage-eyebrow">{{ $labels['treatments_heading'] }}</p>
    <nav class="item-nav">
        @foreach ($treatments->groupBy('category') as $category => $group)
            <p class="item-nav-heading">{{ $terms['category'][$category] ?? Str::headline($category) }}</p>
            @foreach ($group as $item)
                <a href="{{ route('spa.treatment', ['spaSlug' => $spa->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}"
                    data-stage-exit
                    @if (($treatment ?? null)?->slug === $item->slug) aria-current="page" @endif>
                    @if ($item->image_source)
                        <img class="item-nav-thumb" src="{{ $item->image_source }}" alt="" loading="lazy">
                    @endif
                    <span class="item-nav-text">
                        <span class="item-nav-name">{{ $item->translatedName($locale) }}</span>
                        <span class="item-nav-price">{{ $spa->currency }} {{ number_format((float) $item->price, 0, ',', '.') }}@if ($item->is_unavailable)<span class="item-nav-badge">{{ $stageCopy['sold_out'] }}</span>@endif</span>
                    </span>
                </a>
            @endforeach
        @endforeach
    </nav>
    <span class="stage-treatments-footer">{{ $stageCopy['available'] }} · SPA AI</span>
</aside>
