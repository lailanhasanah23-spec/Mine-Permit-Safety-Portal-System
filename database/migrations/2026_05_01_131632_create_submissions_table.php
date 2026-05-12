<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename tables if they exist
        if (Schema::hasTable('simper_submissions') && ! Schema::hasTable('submissions')) {
            Schema::rename('simper_submissions', 'submissions');
        }

        if (Schema::hasTable('simper_submission_files') && ! Schema::hasTable('submission_files')) {
            // Drop foreign key first because it points to old table name
            Schema::table('simper_submission_files', function (Blueprint $table) {
                $table->dropForeign(['submission_id']);
            });

            Schema::rename('simper_submission_files', 'submission_files');

            // Re-add foreign key pointing to new table name
            Schema::table('submission_files', function (Blueprint $table) {
                $table->foreign('submission_id')->references('id')->on('submissions')->cascadeOnDelete();
            });
        }

        // 2. Adjust submissions table
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            }

            if (Schema::hasColumn('submissions', 'simper_type') && ! Schema::hasColumn('submissions', 'item_type')) {
                $table->renameColumn('simper_type', 'item_type');
            } elseif (! Schema::hasColumn('submissions', 'item_type')) {
                $table->string('item_type', 100)->nullable()->after('company_name');
            }

            if (! Schema::hasColumn('submissions', 'item_identifier')) {
                $table->string('item_identifier', 100)->nullable()->after('item_type');
            }

            if (! Schema::hasColumn('submissions', 'item_details')) {
                $table->json('item_details')->nullable()->after('item_identifier');
            }
        });

        // 3. Data Migration: Set default category_id for existing SIMPER submissions
        $simperCategoryId = DB::table('categories')->where('code', 'SIMPER_PERMIT')->value('id');
        if ($simperCategoryId) {
            DB::table('submissions')->whereNull('category_id')->update(['category_id' => $simperCategoryId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'item_type') && ! Schema::hasColumn('submissions', 'simper_type')) {
                $table->renameColumn('item_type', 'simper_type');
            }
            $table->dropColumn(['category_id', 'item_identifier', 'item_details']);
        });

        Schema::table('submission_files', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
        });

        Schema::rename('submission_files', 'simper_submission_files');
        Schema::rename('submissions', 'simper_submissions');

        Schema::table('simper_submission_files', function (Blueprint $table) {
            $table->foreign('submission_id')->references('id')->on('simper_submissions')->cascadeOnDelete();
        });
    }
};
