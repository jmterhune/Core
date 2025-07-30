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
        Schema::create('mediation_instructions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('county_id');
            $table->text('instruction')->nullable();
            $table->unsignedBigInteger('location_type_id')->nullable();
            $table->string('case_type');

            $table->foreign('location_type_id')->references('id')->on('event_types');
            $table->foreign('county_id')->references('id')->on('counties');
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
        Schema::dropIfExists('mediation_instructions');
    }
};
