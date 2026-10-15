@props(['spa', 'locale', 'supportedLocales', 'labels', 'stageCopy', 'narration', 'scene', 'backdrop', 'navItems', 'terms' => [], 'title' => null, 'welcome' => null, 'sidebar' => null, 'nav' => true])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $spa->translatedDescription($locale) }}">
    <title>{{ $title ?? $spa->name.' · AI Spa Concierge' }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="stage-page antialiased" style="--concierge-image: url('{{ $backdrop['avatarImage'] }}'); --stage-focus: {{ $backdrop['focus'] }}; --stage-focus-mobile: {{ $backdrop['focusMobile'] }}">
    <main class="stage" data-stage data-scene="{{ $scene }}" data-page="{{ $scene }}">
        <div class="stage-loader" data-stage-loader role="status"><span class="spa-monogram" aria-hidden="true">S</span><p>{{ $stageCopy['loading'] }}</p></div>
        <img src="{{ $backdrop['image'] }}" alt="" class="stage-image" fetchpriority="high">
        <div class="stage-shade"></div>
        <img src="{{ $backdrop['character'] }}" alt="{{ $stageCopy['assistant'] }}" class="stage-character" data-anchor="right">
        <p class="stage-tour" data-stage-tour aria-live="polite"></p>
        <header class="stage-header">
            <a class="stage-brand" href="{{ route('spa.show', ['spaSlug' => $spa->slug, 'lang' => $locale]) }}" @if($scene !== 'home') data-stage-exit data-tour-line="{{ $narration['tour_home'] }}" @endif>
                <span class="spa-monogram" aria-hidden="true">S</span>
                <span><strong>{{ $spa->name }}</strong><small>WELLNESS SANCTUARY</small></span>
            </a>
            <div class="stage-tools">
                <x-sound-toggle :on="$stageCopy['sound_on']" :off="$stageCopy['sound_off']" />
                <nav aria-label="Language" class="stage-lang">
                    @foreach ($supportedLocales as $code)
                        <a href="?{{ http_build_query([...request()->except('lang'), 'lang' => $code]) }}" lang="{{ $code }}" aria-label="{{ ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ja' => '日本語'][$code] }}" @if($code === $locale) aria-current="true" @endif>{{ $code }}</a>
                    @endforeach
                </nav>
            </div>
        </header>
        <aside class="stage-rail" aria-label="{{ $stageCopy['explore'] }}">
            <p class="stage-eyebrow">{{ $stageCopy['explore'] }}</p>
            <a class="rail-home" aria-label="{{ $locale === 'ja' ? 'サンクチュアリ' : 'Sanctuary' }}" href="{{ route('spa.show', ['spaSlug' => $spa->slug, 'lang' => $locale]) }}" data-stage-exit @if($scene === 'home') aria-current="page" @endif><span class="nav-icon"><x-stage-icon name="leaf" /></span><span>{{ $locale === 'ja' ? 'サンクチュアリ' : 'Sanctuary' }}</span></a>
            <nav class="stage-navigation">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" data-stage-exit data-tour-line="{{ $item['tour'] }}" data-topic="{{ $item['topic'] }}" title="{{ $item['label'] }}"
                       @if($item['key'] === $scene || ($scene === 'treatment' && $item['key'] === 'treatments') || ($scene === 'treatment-room' && $item['key'] === 'rooms') || ($scene === 'facility' && $item['key'] === 'facilities')) aria-current="page" @endif>
                        <span class="nav-icon"><x-stage-icon :name="$item['icon']" /></span><span>{{ $item['label'] }}</span><span class="nav-arrow" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </nav>
            <div class="rail-foot"><span class="rail-status"></span>{{ $stageCopy['available'] }}</div>
        </aside>
        {{ $welcome }}
        {{ $slot }}
        <div class="stage-signature" aria-hidden="true"><span>AI SPA CONCIERGE</span><small>{{ $stageCopy['available'] }}</small></div>
        <div data-chat-widget>@include('spa.chat')</div>
        <noscript><p class="stage-noscript">{{ $locale === 'id' ? 'Aktifkan JavaScript untuk menggunakan chat dan booking.' : ($locale === 'ja' ? 'チャットと予約にはJavaScriptを有効にしてください。' : 'Enable JavaScript to use chat and booking.') }}</p></noscript>
    </main>
</body>
</html>
