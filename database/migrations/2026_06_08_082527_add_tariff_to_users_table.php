<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tariff')->default('БАЗОВЫЙ')->after('is_active');
            $table->timestamp('tariff_expires_at')->nullable()->after('tariff');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tariff', 'tariff_expires_at']);
        });
    }
};