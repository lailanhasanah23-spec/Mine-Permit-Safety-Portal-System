<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('created_by')->constrained('internal_companies');
            }
            if (! Schema::hasColumn('submissions', 'submitted_by_vendor')) {
                $table->boolean('submitted_by_vendor')->default(false)->after('vendor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'vendor_id')) {
                $table->dropForeignKeyIfExists(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
            if (Schema::hasColumn('submissions', 'submitted_by_vendor')) {
                $table->dropColumn('submitted_by_vendor');
            }
        });
    }
};
