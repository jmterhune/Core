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
        Schema::create('timeslot_motions', function (Blueprint $table) {
            $table->id();
            $table->morphs('timeslotable');
            $table->unsignedBigInteger('motion_id');
            $table->timestamps();

            $table->index(array('timeslotable_id', 'timeslotable_type'));
            $table->foreign('motion_id')->references('id')->on('motions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timeslot_motions');
    }
};
