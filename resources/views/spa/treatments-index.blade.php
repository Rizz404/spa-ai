<x-spa-stage :spa="$spa" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$labels['treatments_heading'].' · '.$spa->name">
    <section class="stage-content collection-panel" aria-label="{{ $labels['treatments_heading'] }}">
        <p class="stage-eyebrow">{{ $stageCopy['explore'] }} <span aria-hidden="true">/ 02</span></p>
        <h1>{{ $labels['treatments_heading'] }}</h1>
        @if ($treatmentsNarrations['treatments'])
            @include('spa.narrator', ['text' => $treatmentsNarrations['treatments'], 'key' => 'treatments', 'inline' => true])
        @endif
        <div class="collection-grid">
            @forelse ($treatments as $item)
                <a class="ritual-card" href="{{ route('spa.treatment', ['spaSlug' => $spa->slug, 'itemSlug' => $item->slug, 'lang' => $locale]) }}" data-stage-exit>
                    <div class="ritual-card-art">@if($item->image_source)<img src="{{ $item->image_source }}" alt="{{ $item->translatedName($locale) }}" loading="lazy">@endif<span class="ritual-card-number">{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</span></div>
                    <div class="ritual-card-body"><p class="stage-eyebrow">{{ $terms['category'][$item->category] }}</p><h2>{{ $item->translatedName($locale) }}</h2><p class="ritual-card-detail">{{ $item->duration_minutes }} min <span>·</span> {{ $spa->currency }} {{ number_format((float) $item->price, 0, ',', '.') }}</p><span class="ritual-card-arrow" aria-hidden="true">↗</span></div>
                </a>
            @empty
                <p>{{ $stageCopy['treatments_empty'] }}</p>
            @endforelse
        </div>
    </section>
</x-spa-stage>
