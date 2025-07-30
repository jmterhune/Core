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
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('old_id')->nullable();
            $table->string('description');
            $table->string('case_num_format');
            $table->unsignedBigInteger('county_id');
            $table->unsignedBigInteger('def_attorney_id')->nullable();
            $table->string('plaintiff')->nullable();
            $table->unsignedBigInteger('opp_attorney_id')->nullable();
            $table->string('defendant')->nullable();
            $table->boolean('scheduling')->default(false);
            $table->text('web_policy')->nullable();
            $table->boolean('public_timeslot')->default(0);
            $table->boolean('public_docket')->default(0);
            $table->tinyInteger('public_docket_days')->default(0)->nullable();
            $table->boolean('email_confirmations')->default(0);
            $table->tinyInteger('lagtime')->default(0)->nullable();
            $table->text('custom_email_body')->nullable();
            $table->tinyInteger('twitter_notification')->default(0);
            $table->integer('calendar_weeks')->default(4);
            $table->boolean('auto_extension')->default(false);
            $table->boolean('plaintiff_required')->default(false);
            $table->boolean('defendant_required')->default(false);
            $table->boolean('defendant_attorney_required')->default(false);
            $table->boolean('plaintiff_attorney_required')->default(false);
            $table->boolean('category_print')->default(true);
            $table->tinyInteger('max_lagtime')->nullable();
            $table->text('custom_header')->nullable();
            $table->text('timeslot_header')->nullable();
            $table->timestamps();

            $table->foreign('def_attorney_id')->references('id')->on('attorneys');
            $table->foreign('opp_attorney_id')->references('id')->on('attorneys');
            $table->foreign('county_id')->references('id')->on('counties');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courts');
    }
};
