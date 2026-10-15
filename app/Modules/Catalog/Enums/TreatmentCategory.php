<?php

namespace App\Modules\Catalog\Enums;

/**
 * The treatments and ritual packages offered by a spa.
 */
enum TreatmentCategory: string
{
    case Massage = 'massage';
    case Body = 'body';
    case Facial = 'facial';
    case Ritual = 'ritual';
    case Package = 'package';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
