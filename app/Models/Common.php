<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use DateTime;

class Common
{
    public static function getFormattedDate($date, $fallbackDate)
    {
        Log::info("Date received: $date");

        if (count(explode(":", $date)) === 2) {
            return DateTime::createFromFormat('d/m/Y H:i', $date)->format('Y-m-d H:i:s');
        }
        elseif(count(explode(":", $date)) === 3) {
            return DateTime::createFromFormat('d/m/Y H:i:s', $date)->format('Y-m-d H:i:s');
        }
        elseif(count(explode(":", $date)) === 0){
            return DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d H:i:s');
        }
        else {
            if(empty($date)){
                if(!empty($fallbackDate)){
                    return self::getFormattedDate($fallbackDate, '');
                }
            }
        }

        return '';
    }
}