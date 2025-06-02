<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('theories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->text('content');
            $table->integer('position_order')->default(1);
            $table->timestamps();

            // Relations (facultatif si lessons existe)
            // $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theories');
    }
}
