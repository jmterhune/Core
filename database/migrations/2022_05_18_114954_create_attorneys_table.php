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
        Schema::create('attorneys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bar_num');
            $table->string('phone')->nullable();
            $table->boolean('scheduling')->default(true);
            $table->string('notes')->nullable();
            $table->boolean('enabled')->default(false);
            $table->string('password');
            $table->string('password_changed_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('attorneys');
    }
};
