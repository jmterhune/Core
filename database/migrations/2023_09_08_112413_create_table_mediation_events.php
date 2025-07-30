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
        Schema::create('mediation_events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('e_c_id')->unsigned()->nullable();
            $table->bigInteger('e_m_id')->unsigned()->nullable();
            $table->boolean('e_def_failedtoap')->nullable();
            $table->boolean('e_pltf_failedtoap')->nullable();
            $table->bigInteger('e_outcome_id')->unsigned()->nullable();
            $table->datetime('e_sch_datetime')->nullable();
            $table->decimal('e_sch_length',8,2)->default(0)->nullable();
            $table->decimal('e_med_fee',8,2)->default(0)->nullable();
            $table->decimal('e_pltf_chg',8,2)->default(0)->nullable();
            $table->decimal('e_def_chg',8,2)->default(0)->nullable();
            $table->string('e_subject')->nullable();
            $table->text('e_notes')->nullable();
            $table->timestamps();
            $table->double('e_med_per_hr',8,2)->nullable();

            $table->foreign('e_c_id')->references('id')->on('mediation_cases');
            $table->foreign('e_m_id')->references('id')->on('mediation_mediators');
            $table->foreign('e_outcome_id')->references('id')->on('mediation_outcome');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mediation_events');
    }
};
