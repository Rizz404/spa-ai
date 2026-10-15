<x-spa-stage :spa="$spa" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['rooms_heading'].' · '.$spa->name">
    <section class="stage-content collection-panel" aria-label="{{ $labels['rooms_heading'] }}">
        <p class="stage-eyebrow">{{ $stageCopy['explore'] }} <span aria-hidden="true">/ 03</span></p>
        <h1>{{ $labels['rooms_heading'] }}</h1>
        @if ($roomsNarrations['rooms'])
            @include('spa.narrator', ['text' => $roomsNarrations['rooms'], 'key' => 'rooms', 'inline' => true])
        @endif
        <div class="collection-grid">
            @forelse ($treatmentRooms as $item)
                <a class="ritual-card" href="{{ route('spa.treatment-room', ['spaSlug' => $spa->slug, 'areaSlug' => $item->slug, 'lang' => $locale]) }}" data-stage-exit>
                    <div class="ritual-card-art">@if($item->images->first()?->image_source)<img src="{{ $item->images->first()?->image_source }}" alt="{{ $item->translatedName($locale) }}" loading="lazy">@endif<span class="ritual-card-number">{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</span></div>
                    <div class="ritual-card-body"><p class="stage-eyebrow">{{ $stageCopy['area_counter'] }}</p><h2>{{ $item->translatedName($locale) }}</h2><p class="ritual-card-detail">{{ $terms['room_type'][$item->room_type] }} <span>·</span> {{ $item->min_guests }}–{{ $item->max_guests }} {{ $stageCopy['guests_unit'] }}</p><span class="ritual-card-arrow" aria-hidden="true">↗</span></div>
                </a>
            @empty
                <p>{{ $stageCopy['rooms_empty'] }}</p>
            @endforelse
        </div>
    </section>
</x-spa-stage>
