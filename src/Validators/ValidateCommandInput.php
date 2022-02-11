<?php

namespace App\Validators;

use DateTime;

class ValidateCommandInput
{
    /**
     * @param $date
     * @return bool
     */
    public static function date($date) : bool
    {
        $format = 'Y-m-d';
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) === $date;
    }

    /**
     * @param $hour
     * @return bool
     */
    public static function hour($hour) : bool
    {
        return is_numeric($hour) && (int)$hour <= 23 && (int)$hour >= 0;
    }
}