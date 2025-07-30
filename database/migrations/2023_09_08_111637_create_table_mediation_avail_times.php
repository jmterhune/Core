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
        Schema::create('mediation_avail_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('at_m_id')->nullable();
            $table->string('at_time')->nullable();
            $table->date('at_begin')->nullable();
            $table->date('at_end')->nullable();
            $table->boolean('at_available')->nullable();
            $table->smallinteger('at_weekday')->nullable();
            $table->timestamps();
            $table->foreign('at_m_id')->references('id')->on('mediation_mediators');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mediation_avail_times');
    }
};
