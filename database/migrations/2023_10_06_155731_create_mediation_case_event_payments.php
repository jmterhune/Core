<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mediation_case_event_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('p_c_id')->unsigned()->nullable();
            $table->bigInteger('p_e_id')->unsigned()->nullable();
            $table->decimal('amount_paid',8,2)->default(0)->nullable();
            $table->string('paid_by',10)->nullable();
            $table->date('paid_on')->nullable();
            $table->timestamps();

            $table->foreign('p_c_id')->references('id')->on('mediation_cases');
            $table->foreign('p_e_id')->references('id')->on('mediation_events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mediation_case_event_payments');
    }
};
