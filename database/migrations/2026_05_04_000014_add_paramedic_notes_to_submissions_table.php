<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions', 'paramedic_notes')) {
                $table->text('paramedic_notes')->nullable()->after('she_notes');
            }
            if (! Schema::hasColumn('submissions', 'paramedic_verified_at')) {
                $table->timestamp('paramedic_verified_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['paramedic_notes', 'paramedic_verified_at']);
        });
    }
};
