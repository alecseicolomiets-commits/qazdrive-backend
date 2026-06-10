<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->nullable()->after('id');
            $table->string('city', 100)->nullable()->after('location');
            $table->string('drive_type', 50)->nullable()->after('transmission');
            $table->string('condition', 50)->nullable()->after('drive_type');
            $table->integer('current_price')->nullable()->after('current_bid');
            $table->integer('start_price')->nullable()->after('starting_price');
            $table->string('status', 20)->default('active')->after('is_finished');

            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn(['seller_id', 'city', 'drive_type', 'condition', 'current_price', 'start_price', 'status']);
        });
    }
};