<x-spa-stage :spa="$spa" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$treatmentRoom->translatedName($locale).' · '.$spa->name">
    @if($treatmentRooms->isNotEmpty())
        <x-slot:sidebar>@include('spa.rooms-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('spa.rooms-scene')
    </div>
</x-spa-stage>
