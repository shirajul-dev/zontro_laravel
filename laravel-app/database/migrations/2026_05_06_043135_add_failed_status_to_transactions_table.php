<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE pp_transaction MODIFY COLUMN status ENUM('completed', 'pending', 'refunded', 'initiated', 'canceled', 'failed') NOT NULL DEFAULT 'initiated'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pp_transaction MODIFY COLUMN status ENUM('completed', 'pending', 'refunded', 'initiated', 'canceled') NOT NULL DEFAULT 'initiated'");
    }
};
