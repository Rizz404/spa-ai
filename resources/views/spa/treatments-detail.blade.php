<x-spa-stage :spa="$spa" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :title="$treatment->translatedName($locale).' · '.$spa->name">
    @if($treatments->isNotEmpty())
        <x-slot:sidebar>@include('spa.treatments-nav')</x-slot:sidebar>
    @endif

    <div class="stage-panels">
        @include('spa.treatments-scene')
    </div>
</x-spa-stage>
