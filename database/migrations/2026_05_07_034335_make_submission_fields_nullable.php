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
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('applicant_name', 190)->nullable()->change();
            $table->string('applicant_nik', 50)->nullable()->change();
            $table->string('company_name', 190)->nullable()->change();
            $table->string('item_type', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('applicant_name', 190)->nullable(false)->change();
            $table->string('applicant_nik', 50)->nullable(false)->change();
            $table->string('company_name', 190)->nullable(false)->change();
            $table->string('item_type', 100)->nullable(false)->change();
        });
    }
};
