<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
