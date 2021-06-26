<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DigiplusTickets extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'case_number',
        'created_on',
        'case_type',
        'case_category',
        'case_sub_category',
        'description',
        'status',
        'account_number',
        'customer_type',
        'customer_name',
        'primary_mobile_number',
        'city',
        'created_by',
        'modified_by',
        'modified_on',
        'case_title',
        'pivot_date_created',
        'tier_filter',
        'resolution_time',
        'sla',
        'week',
        'escalation_team',
    ];

    public static function getReportDetails($createdOn1, $createdOn2){
        try{
            $reportDetails = DB::select("SELECT 
                                        DATE(created_on) as created_on,
                                        COUNT(sla) as case_count,
                                        sla
                                        FROM `digiplus_tickets` 
                                        WHERE created_on BETWEEN '$createdOn1' AND '$createdOn2'
                                        Group BY DATE(created_on), sla
                                        ");
        
            if($reportDetails){
                return $reportDetails;
            }
        }
        catch(Exception $error){
            Log::error("Error trying to get Reports by dates created: " . $error->getMessage());
        }

        return [];


    }
}
