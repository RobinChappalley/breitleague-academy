<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('challenger_id');
            $table->unsignedBigInteger('challenged_id');
            $table->boolean('has_challenger_won')->nullable();
            $table->boolean('has_challenger_accepted')->default(false);
            $table->boolean('has_challenged_accepted')->default(false);
            $table->timestamp('challenger_init_date')->nullable();
            $table->json('questions_id')->nullable();
            $table->json('challenger_summary')->nullable();
            $table->json('challenged_summary')->nullable();
            $table->timestamps();

            $table->foreign('challenger_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('challenged_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
