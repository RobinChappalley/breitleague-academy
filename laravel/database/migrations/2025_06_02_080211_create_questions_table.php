<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('content_default');
            $table->text('content_if_TF')->nullable();
            $table->text('content_if_blank')->nullable();
            $table->boolean('isMatchable')->default(false);
            $table->unsignedBigInteger('checkpoint_id');
            $table->unsignedBigInteger('theory_id');
            $table->integer('position_order')->default(1);
            $table->json('correct_answer_text');
            $table->timestamps();

            // $table->foreign('theory_id')->references('id')->on('theories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
}
