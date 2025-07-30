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
        Schema::create('mediation_documents', function (Blueprint $table) {
            $table->id();
            $table->string('d_title',100);
            $table->date('d_valid_date');
            $table->string('d_ext',5);
            $table->string('d_original',100);
            $table->unsignedBigInteger('d_u_id');
            $table->string('d_fname',50);
            $table->timestamps();
            
            $table->foreign('d_u_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mediation_documents');
    }
};
