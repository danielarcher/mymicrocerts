<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAttemptDrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attempt_drawn_questions', function (Blueprint $table) {
            $table->json('correct_answer')->nullable();
            $table->json('received_answer')->nullable();
            $table->boolean('is_correct')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('category', function (Blueprint $table) {
            $table->removeColumn('correct_answer');
            $table->removeColumn('received_answer');
            $table->removeColumn('is_correct');
        });
    }
}
