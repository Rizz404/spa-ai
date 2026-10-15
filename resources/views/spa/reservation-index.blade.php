<x-spa-stage :spa="$spa" :locale="$locale" :supported-locales="$supportedLocales" :labels="$labels" :stage-copy="$stageCopy" :narration="$narration" :scene="$scene" :backdrop="$backdrop" :nav-items="$navItems" :terms="$terms" :nav="false" :title="$labels['reservation_heading'].' · '.$spa->name">
    <div class="stage-panels">
        @include('spa.reservation')
    </div>
</x-spa-stage>
