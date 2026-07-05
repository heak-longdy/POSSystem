<?php
namespace App\Functions;

use Carbon\Carbon;

class DateFormat
{
    public static function create($date, $format = null)
    {
        if (!$format) {
            return [
                Carbon::parse($date)->isoFormat('DD MMM, Y'),
                Carbon::parse($date)->isoFormat('hh:mm a'),
            ];
        } else {
            return Carbon::parse($date ?? Carbon::now())->isoFormat($format);
        }
    }
}
