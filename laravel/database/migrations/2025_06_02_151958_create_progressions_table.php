<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('progressions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('last_lesson_id')->nullable();
            $table->unsignedBigInteger('last_checkpoint_id')->nullable();
            $table->json('idofquestionscorrectlyanswered')->nullable(); // pour stocker un tableau d'IDs
            $table->timestamps();

            // Foreign keys (à ajuster selon tes tables existantes)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Facultatif : désactive si pas encore de table leçon/checkpoint
            // $table->foreign('last_lesson_id')->references('id')->on('lessons')->onDelete('set null');
            // $table->foreign('last_checkpoint_id')->references('id')->on('checkpoints')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progressions');
    }
};
