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
        Schema::create('template_timeslots', function (Blueprint $table) {
            $table->id();
            $table->dateTime('end');
            $table->dateTime('start');
            $table->integer('day');
            $table->string('description')->nullable();
            $table->boolean('allDay')->default(false);
            $table->integer('quantity');
            $table->integer('duration');
            $table->boolean('blocked')->default(false);
            $table->boolean('public_block')->default(false);
            $table->string('block_reason')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('court_template_id')->nullable();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('court_template_id')->references('id')->on('court_templates');
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
        Schema::dropIfExists('template_timeslots');
    }
};
