<?php

namespace App\Modules\Reservation\Enums;

/**
 * Occasions a guest can mention so the team can prepare the table.
 */
enum Occasion: string
{
    case Birthday = 'birthday';
    case SelfCare = 'self_care';
    case Couple = 'couple';
    case Gift = 'gift';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
