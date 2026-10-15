<?php

namespace App\Modules\Rooms\Enums;

/**
 * Where in the spa a rooms area is.
 */
enum RoomType: string
{
    case Single = 'single';
    case Couple = 'couple';
    case Garden = 'garden';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
