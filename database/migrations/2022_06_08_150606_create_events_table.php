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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('case_num')->nullable();
            $table->text('notes')->nullable();

            $table->string('plaintiff')->nullable();
            $table->string('defendant')->nullable();

            $table->unsignedBigInteger('motion_id')->nullable();
            $table->unsignedBigInteger('attorney_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->boolean('reminder')->default(0)->comment("0:reminder off , 1:reminder on");

            $table->unsignedBigInteger('opp_attorney_id')->nullable();

            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('owner_type')->nullable();
            $table->boolean('addon')->nullable();
            $table->string('plaintiff_email', 250)->nullable();
            $table->string('defendant_email', 250)->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->longText('template')->nullable();
            $table->string('telephone')->nullable();
            $table->string('custom_motion')->nullable();
            $table->timestamps();

            $table->foreign('attorney_id')->references('id')->on('attorneys');
            $table->foreign('opp_attorney_id')->references('id')->on('attorneys');
            $table->foreign('type_id')->references('id')->on('event_types');
            $table->foreign('status_id')->references('id')->on('event_statuses');
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
        Schema::dropIfExists('events');
    }
};
