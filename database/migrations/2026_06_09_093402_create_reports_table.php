<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reason', 255);
            $table->text('comment')->nullable();
            $table->string('status', 20)->default('pending'); // pending, reviewed, dismissed
            $table->timestamps();

            $table->foreign('car_id')->references('id')->on('cars')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};