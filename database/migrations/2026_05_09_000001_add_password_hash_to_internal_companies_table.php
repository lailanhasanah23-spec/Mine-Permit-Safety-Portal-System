<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_companies', function (Blueprint $table) {
            if (! Schema::hasColumn('internal_companies', 'password_hash')) {
                $table->string('password_hash')->nullable()->after('is_manual_input_allowed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internal_companies', function (Blueprint $table) {
            if (Schema::hasColumn('internal_companies', 'password_hash')) {
                $table->dropColumn('password_hash');
            }
        });
    }
};
