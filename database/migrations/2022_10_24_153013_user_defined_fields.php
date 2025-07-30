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
        Schema::create('user_defined_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('court_id');
            $table->string('field_name');
            $table->string('field_type');
            $table->string('alignment');
            $table->string('default_value')->default(NULL)->nullable();
            $table->tinyInteger('required')->default(0);
            $table->tinyInteger('yes_answer_required')->default(0);
            $table->tinyInteger('display_on_docket')->default(0);
            $table->tinyInteger('display_on_schedule')->default(0);
            $table->tinyInteger('use_in_attorany_scheduling')->default(0);
            $table->string('old_id')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('user_defined_fields');
    }
};
