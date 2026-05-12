<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('google_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // Drop unique on service_name
            $table->dropUnique('google_tokens_service_name_unique');

            // Add unique on (service_name, user_id)
            $table->unique(['service_name', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_tokens', function (Blueprint $table) {
            $table->dropUnique(['service_name', 'user_id']);
            $table->unique('service_name');
            $table->dropColumn('user_id');
        });
    }
};
