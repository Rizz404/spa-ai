<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spa AI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page opening-page antialiased" style="--concierge-image: url('{{ $avatarImage }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage" data-scene="home">
        <div class="stage-loader" data-stage-loader role="status"><span class="spa-monogram" aria-hidden="true">S</span><p>{{ $opening['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="" class="stage-character" data-anchor="right">

        <header class="stage-header">
            <span class="stage-brand">
                <span class="spa-monogram" aria-hidden="true">S</span>
                <span><strong>Spa AI</strong><small>WELLNESS SANCTUARY</small></span>
            </span>
            <div class="stage-tools">
                <x-sound-toggle :on="$opening['sound_on']" :off="$opening['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?lang={{ $code }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        <section class="opening-panel" aria-labelledby="opening-title">
            <p class="stage-eyebrow">AI CONCIERGE</p>
            <h1 id="opening-title">{{ $opening['welcome'] }}<br><em>Spa AI</em></h1>
            <p class="opening-tagline">{{ $opening['tagline'] }}</p>

            @if ($spas->count() === 1)
                @php
                    $spaSlug = $spas->first()->slug;
                    $openingLinks = [
                        'treatments' => route('spa.treatments', ['spaSlug' => $spaSlug, 'lang' => $locale]),
                        'rooms' => route('spa.rooms', ['spaSlug' => $spaSlug, 'lang' => $locale]),
                        'reservation' => route('spa.reservation', ['spaSlug' => $spaSlug, 'lang' => $locale]),
                        'staff' => route('spa.staff', ['spaSlug' => $spaSlug, 'lang' => $locale]),
                    ];
                @endphp
                <ul class="opening-links">
                    @foreach ($openingLinks as $key => $href)
                        <li><a data-stage-exit href="{{ $href }}"><span>{{ $opening[$key] }}</span><span aria-hidden="true">›</span></a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <p class="opening-note">BOTANICAL RITUALS · A SPACE TO BREATHE</p>
        <div class="opening-enter">
            @forelse ($spas as $spa)
                <a href="{{ route('spa.show', ['spaSlug' => $spa->slug, 'lang' => $locale]) }}" class="opening-button" data-stage-exit>
                    {{ str_replace(':name', $spa->name, $opening['enter']) }} <span aria-hidden="true">→</span>
                </a>
            @empty
                <p class="opening-empty">{{ $opening['empty'] }}</p>
            @endforelse
        </div>
    </main>
</body>
</html>
