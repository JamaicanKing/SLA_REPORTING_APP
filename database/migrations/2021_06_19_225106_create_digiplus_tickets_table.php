<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDigiplusTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('digiplus_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->dateTime('created_on');
            $table->string('case_type');
            $table->string('case_category');
            $table->string('case_sub_category');
            $table->string('description');
            $table->string('status');
            $table->bigInteger('account_number');
            $table->string('customer_type');
            $table->bigInteger('primary_mobile_number');
            $table->string('city');
            $table->string('created_by');
            $table->string('modified_by');
            $table->dateTime('modified_on');
            $table->string('case_title');
            $table->string('pivot_date_created');
            $table->string('tier_filter');
            $table->decimal('resolution_time',8,5);
            $table->string('sla');
            $table->string('week');
            $table->string('escalation_team');
            $table->timestamps();

            //indexes
            $table->index(['created_on', 'sla', 'tier_filter']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('digiplus_tickets');
    }
}
