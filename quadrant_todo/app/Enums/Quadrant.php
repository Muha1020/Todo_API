<?php

namespace App\Enums;

enum Quadrant: string
{
    case ImportantUrgent = 'important_urgent';              // Q1: do it now
    case ImportantNotUrgent = 'important_not_urgent';       // Q2: schedule it
    case NotImportantUrgent = 'not_important_urgent';       // Q3: delegate it
    case NotImportantNotUrgent = 'not_important_not_urgent';// Q4: remove it if possible

    /** The four keys, in priority order Q1 to Q4. */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}