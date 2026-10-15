<x-spa-stage :spa="$spa" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms">
    <x-slot:welcome>
        <section class="stage-welcome">
            <p class="stage-eyebrow">{{ $locale === 'id' ? 'RITUAL UNTUK DIRI ANDA' : ($locale === 'ja' ? 'あなたのためのリチュアル' : 'RITUALS FOR YOUR WELLBEING') }}</p>
            <h1>{{ $stageCopy['welcome'] }}<br><em>{{ $stageCopy['counter'] }}.</em></h1>
            <p>{{ $stageCopy['intro'] }}</p>
            <div class="welcome-actions">
                <a class="stage-action" href="{{ route('spa.treatments', ['spaSlug' => $spa->slug, 'lang' => $locale]) }}" data-stage-exit data-tour-line="{{ $narration['tour_treatments'] }}">{{ $labels['chip_treatments'] }} <span aria-hidden="true">↗</span></a>
                <a class="welcome-link" href="{{ route('spa.reservation', ['spaSlug' => $spa->slug, 'lang' => $locale]) }}" data-stage-exit>{{ $labels['reservation_heading'] }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="welcome-facts"><span>{{ $stageCopy['hours'] }}<strong>{{ substr($spa->opening_time, 0, 5) }} — {{ substr($spa->closing_time, 0, 5) }}</strong></span><span>{{ $stageCopy['location'] }}<strong>{{ $spa->city }} · {{ $spa->country }}</strong></span></div>
        </section>
    </x-slot:welcome>
    <p class="stage-edition">01 <span>/</span> {{ $spa->name }} — {{ $locale === 'id' ? 'Ruang untuk bernapas' : ($locale === 'ja' ? '深呼吸できる空間' : 'A space to breathe') }}</p>
</x-spa-stage>
