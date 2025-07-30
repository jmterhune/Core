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
        Schema::create('court_timeslots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('court_id')->nullable();
            $table->unsignedBigInteger('timeslot_id')->nullable();

            $table->foreign('court_id')->references('id')->on('courts');
            $table->foreign('timeslot_id')->references('id')->on('timeslots');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('court_timeslots');
    }
};
