<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('email_submission_templates', 'recipient_cc')) {
            Schema::table('email_submission_templates', function (Blueprint $table): void {
                $table->string('recipient_cc', 500)->nullable()->after('submission_type');
                $table->string('recipient_bcc', 500)->nullable()->after('recipient_cc');
            });
        }

        $now = now();

        DB::table('email_submission_templates')
            ->where('template_code', 'simper-perpanjangan-default')
            ->update([
                'recipient_cc' => null,
                'recipient_bcc' => null,
                'updated_at' => $now,
            ]);

        DB::table('email_submission_templates')
            ->where('template_code', 'simper-new-hire-default')
            ->update([
                'recipient_cc' => null,
                'recipient_bcc' => null,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('email_submission_templates', 'recipient_bcc')) {
            Schema::table('email_submission_templates', function (Blueprint $table): void {
                $table->dropColumn('recipient_bcc');
            });
        }

        if (Schema::hasColumn('email_submission_templates', 'recipient_cc')) {
            Schema::table('email_submission_templates', function (Blueprint $table): void {
                $table->dropColumn('recipient_cc');
            });
        }
    }
};
