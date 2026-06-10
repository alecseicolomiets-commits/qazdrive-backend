<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'tariff')) {
                $table->enum('tariff', ['base', 'standard', 'drive', 'business', 'vip'])
                      ->default('base')
                      ->after('role');
            }
            if (!Schema::hasColumn('users', 'tariff_expires_at')) {
                $table->timestamp('tariff_expires_at')->nullable()->after('tariff');
            }
            if (!Schema::hasColumn('users', 'balance')) {
                $table->bigInteger('balance')->default(0)->after('tariff_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tariff', 'tariff_expires_at', 'balance']);
        });
    }
};