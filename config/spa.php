<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spa experience
    |--------------------------------------------------------------------------
    |
    | Guest-facing settings shared by every spa. Per-spa values (name,
    | currency, opening hours, default locale) live in the spas table.
    | Provider connection values stay in config/services.php under
    | "local_llm"; AI budgets are in config/assistant.php.
    |
    */

    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),

    'booking_slots' => ['10:00', '12:00', '14:00', '16:00', '18:00'],

    'locales' => ['id', 'en', 'ja'],

    'default_locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Stage artwork
    |--------------------------------------------------------------------------
    |
    | The small round picture of the concierge used beside chat and narration,
    | and the artwork per scene: a plain illustrated background plus one
    | cut-out of the concierge layered in front, so she can stand clear of the
    | panels. Scenes without artwork of their own borrow another scene's.
    |
    */

    'avatar_image' => 'images/spa/concierge.png',

    'scenes' => [
        'home' => ['image' => 'images/spa/sanctuary.png', 'character' => 'images/spa/concierge.png', 'focus' => 'center', 'focusMobile' => '65% center', 'anchor' => 'right', 'text' => 'top'],
        'treatments' => ['image' => 'images/spa/treatment.png', 'character' => 'images/spa/concierge.png', 'focus' => 'center', 'focusMobile' => '65% center', 'anchor' => 'right', 'text' => 'top'],
        'rooms' => ['image' => 'images/spa/room.png', 'character' => 'images/spa/concierge.png', 'focus' => 'center', 'focusMobile' => '65% center', 'anchor' => 'right', 'text' => 'top'],
        'facilities' => ['image' => 'images/spa/garden.png', 'character' => 'images/spa/concierge.png', 'focus' => 'center', 'focusMobile' => '65% center', 'anchor' => 'right', 'text' => 'top'],
        'info' => ['image' => 'images/spa/sanctuary.png', 'character' => 'images/spa/concierge.png', 'focus' => 'center', 'focusMobile' => '65% center', 'anchor' => 'right', 'text' => 'top'],
        'staff' => ['image' => 'images/spa/sanctuary.png', 'character' => 'images/spa/concierge.png', 'focus' => 'center', 'focusMobile' => '65% center', 'anchor' => 'right', 'text' => 'top'],
        'reservation' => ['image' => 'images/spa/room.png', 'character' => 'images/spa/concierge.png', 'focus' => 'center', 'focusMobile' => '65% center', 'anchor' => 'right', 'text' => 'top'],
    ],

    'scene_artwork' => [
        'treatment' => 'treatments',
        'treatment-room' => 'rooms',
        'facility' => 'facilities',
    ],

];
