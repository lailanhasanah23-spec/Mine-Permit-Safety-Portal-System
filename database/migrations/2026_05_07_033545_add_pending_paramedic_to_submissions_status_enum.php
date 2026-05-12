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
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('pending_hrga', 'pending_tod', 'pending_paramedic', 'pending_she', 'approved', 'rejected') NOT NULL DEFAULT 'pending_hrga'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This might fail if data with 'pending_paramedic' already exists.
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM('pending_hrga', 'pending_tod', 'pending_she', 'approved', 'rejected') NOT NULL DEFAULT 'pending_hrga'");
    }
};
