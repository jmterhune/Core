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
        Schema::create('mediation_cases', function (Blueprint $table) {
            $table->id();
            $table->string('c_caseno')->unique();
            $table->bigInteger('c_div')->unsigned()->nullable();

            $table->bigInteger('c_Pltf_a_id')->unsigned()->nullable();
            $table->bigInteger('c_def_a_id')->unsigned()->nullable();

            $table->string('c_type', 50)->nullable();
            $table->text('c_otherm_text')->nullable();
            $table->text('c_cmmts')->nullable();
            $table->text('c_sch_notes')->nullable();
            $table->unsignedBigInteger('location_type_id');

            $table->timestamps();
            $table->foreign('location_type_id')->references('id')->on('event_types');
            $table->foreign('c_div')->references('id')->on('courts');


            // Didn't add
            $table->boolean('injunction')->default(false);
            $table->boolean('petitioner')->default(false);
            $table->boolean('respondent')->default(false);
            $table->boolean('previous')->default(false);
            $table->string('previous_case_num')->nullable();
            $table->string('origin')->nullable();
            $table->string('previous_case_tel')->nullable();
            $table->string('previous_case_email')->nullable();
            $table->string('p_signature')->nullable();
            $table->string('d_signature')->nullable();

            $table->boolean('approved')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->string('form_type')->nullable();

            $table->string('gal')->nullable();
            $table->string('gal_tel')->nullable();
            $table->string('gal_add')->nullable();
            $table->string('gal_email')->nullable();

            $table->string('f_issues')->nullable();
            $table->decimal('e_pltf_chg','10','2')->nullable();
            $table->decimal('e_pltf_annl_chg','10','2')->nullable();
            $table->decimal('e_def_chg','10','2')->nullable();
            $table->decimal('e_def_annl_chg','10','2')->nullable();

            $table->string('f_issues_other_notes')->nullable();
            $table->text('approval_reason')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->text('availability')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mediation_cases');
    }
};
