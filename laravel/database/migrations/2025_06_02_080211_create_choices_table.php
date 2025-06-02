<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChoicesTable extends Migration
{
    public function up(): void
    {
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('text_answer');
            $table->string('img_answer')->nullable();
            $table->timestamps();

            // $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
}
