<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('reward_id')->nullable();
            $table->timestamps();

            $table->foreign('reward_id')->references('id')->on('rewards')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
