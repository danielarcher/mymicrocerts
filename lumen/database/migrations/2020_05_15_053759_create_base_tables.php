<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateBaseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('currency');
            $table->double('price');
            $table->integer('credits');
            $table->integer('api_requests_per_hour');
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('contract', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->double('price');
            $table->integer('credits_total');
            $table->integer('credits_used')->default(0);
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('company', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('country');
            $table->string('email');
            $table->string('contact_name');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('candidate', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->nullable();
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('role')->default('guest');
            $table->boolean('verified')->default(false);
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('exam', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('success_score_in_percent')->default(100);
            $table->integer('max_time_in_minutes')->default(10);
            $table->string('max_attempts_per_candidate')->default(3);
            $table->boolean('active')->default(true);
            $table->boolean('visible_internal')->default(true);
            $table->boolean('visible_external')->default(false);
            $table->boolean('private')->default(false);
            $table->text('access_id')->nullable();
            $table->text('link')->nullable();
            $table->text('access_password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('attempt', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_id');
            $table->uuid('candidate_id');
            $table->integer('score_in_percent')->nullable();
            $table->integer('score_absolute')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->boolean('approved')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('certificate', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_id');
            $table->uuid('candidate_id');
            $table->integer('score_in_percent');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('question', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('exam_id');
            $table->uuid('company_id');
            $table->integer('number')->nullable();
            $table->text('description');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('option', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id');
            $table->text('text');
            $table->boolean('correct')->default(false);
            $table->softDeletes();
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
        Schema::dropIfExists('attempt');
        Schema::dropIfExists('certificate');
        Schema::dropIfExists('question');
        Schema::dropIfExists('exam');
        Schema::dropIfExists('candidate');
        Schema::dropIfExists('company');
        Schema::dropIfExists('plan');
        Schema::dropIfExists('option');
        Schema::dropIfExists('contract');
    }
}
