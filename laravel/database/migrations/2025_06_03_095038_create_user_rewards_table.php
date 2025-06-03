<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reward_id');
            $table->boolean('is_favourite')->default(false);
            $table->timestamp('acquired_at')->nullable();
            $table->timestamps();

            // Clés étrangères
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reward_id')->references('id')->on('rewards')->onDelete('cascade');

            // Un utilisateur ne peut pas avoir deux fois la même reward
            $table->unique(['user_id', 'reward_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_rewards');
    }
};
