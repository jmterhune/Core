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
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ticket_number',200)->unique();
            $table->longText('subject');
            $table->longText('issue');
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('priority_id')->default(1);
            $table->string('created_user_type',100);
            $table->longText('comment');
            $table->unsignedBigInteger('status_id')->default(1);
            $table->string('file',255)->nullable();
            $table->timestamps();
            $table->foreign('status_id')->references('id')->on('tickets_statuses');
            $table->foreign('priority_id')->references('id')->on('tickets_priority');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
