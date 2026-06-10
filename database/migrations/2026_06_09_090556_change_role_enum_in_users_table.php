<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user','moderator','seller','buyer') NOT NULL DEFAULT 'buyer'");
    }

    public function down(): void {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user','moderator') NOT NULL DEFAULT 'user'");
    }
};