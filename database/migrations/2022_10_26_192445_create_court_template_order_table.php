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
        Schema::create('court_template_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('court_id');
            $table->integer('order')->nullable();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->boolean('auto');
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('court_templates');
            $table->foreign('court_id')->references('id')->on('courts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('court_template_order');
    }
};
