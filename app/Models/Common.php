<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use DateTime;

class Common
{
    public static function getFormattedDate($date)
    {
        Log::info("Date received: $date");

        if (count(explode(":", $date)) === 2) {
            return DateTime::createFromFormat('m/d/Y H:i', $date)->format('Y-m-d H:i:s');
        }
        elseif(count(explode(":", $date)) === 3) {
            return DateTime::createFromFormat('m/d/Y H:i:s', $date)->format('Y-m-d H:i:s');
        }
        elseif(count(explode(":", $date)) === 0){
            return DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d H:i:s');
        }

        return 'No Date Found';
    }

    public static function getTierFilter($createdBy, $modifiedBy, $status){

        if( $createdBy == $modifiedBy && $status == 'Resolved'){

            $tierFilter = 'T1';

       }elseif($createdBy != $modifiedBy && $status == 'Resolved'){

           $tierFilter = 'T2';

       }elseif($status == 'Active'){

           $tierFilter = 'Active';

       }else{
           $tierFilter = 'No status Found';
       }

       return $tierFilter;

    }

    public static function getResolutionTime($runDate, $tierFilter, $createdOn, $modifiedOn){
        $modifiedOn = new DateTime($modifiedOn);
        $createdOn = new DateTime($createdOn);
        $runDate = new DateTime($runDate);

        if($tierFilter == 'T1' || $tierFilter == 'T2'){

            $resolutionTime = $modifiedOn->diff($createdOn);
            $resolutionTime = $resolutionTime->format('%a');

        }elseif($tierFilter == 'Active'){

            $resolutionTime = $runDate->diff($createdOn);
            $resolutionTime = $resolutionTime->format('%a');
        }else{

            $resolutionTime = 'No Resolution Time Found';
        }

        return $resolutionTime;
    }

    public static function getSla($resolutionTime){

        if($resolutionTime <= '1'){
            
            $sla = '24 hours';

        }elseif($resolutionTime > '3'){

            $sla = '+72 hours';

        }elseif($resolutionTime > '1' || $resolutionTime <= '2'){

            $sla = '24 to 48 hours';

        }elseif($resolutionTime > '2' || $resolutionTime <= '3'){

            $sla = '48 to 72 hours';
        }else{

            $sla = 'No SLA found';
        }

        return $sla;
    }
}