<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('make', 100);
            $table->string('model', 100);
            $table->integer('year');
            $table->string('vin', 50)->unique();
            $table->string('location', 100);
            $table->text('description')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('transmission', 50)->nullable();
            $table->string('fuel_type', 50)->nullable();
            $table->decimal('engine_volume', 3, 1)->nullable();
            $table->integer('power_hp')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->boolean('is_live')->default(false);
            $table->boolean('is_top')->default(false);
            $table->boolean('is_finished')->default(false);
            $table->bigInteger('current_bid')->default(0);
            $table->bigInteger('starting_price');
            $table->timestamp('ends_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index('make');
            $table->index('created_at');
            $table->index('ends_at');
            $table->index('is_live');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};