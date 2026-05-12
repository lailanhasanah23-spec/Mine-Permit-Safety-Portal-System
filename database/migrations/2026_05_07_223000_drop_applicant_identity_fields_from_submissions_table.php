<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'applicant_nik')) {
                $table->dropColumn('applicant_nik');
            }

            if (Schema::hasColumn('submissions', 'company_name')) {
                $table->dropColumn('company_name');
            }

            if (Schema::hasColumn('submissions', 'applicant_department')) {
                $table->dropColumn('applicant_department');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions', 'applicant_nik')) {
                $table->string('applicant_nik', 50)->nullable()->after('applicant_name');
            }

            if (! Schema::hasColumn('submissions', 'company_name')) {
                $table->string('company_name', 190)->nullable()->after('applicant_nik');
            }

            if (! Schema::hasColumn('submissions', 'applicant_department')) {
                $table->string('applicant_department', 190)->nullable()->after('company_name');
            }
        });
    }
};
