<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_submission_templates')) {
            Schema::create('email_submission_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_code', 120)->unique();
                $table->string('template_name', 190);
                $table->enum('submission_type', ['perpanjangan', 'new_hire', 'general'])->default('general');
                $table->string('recipient_cc', 500)->nullable();
                $table->string('recipient_bcc', 500)->nullable();
                $table->string('subject_template', 190);
                $table->longText('body_template');
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();

                $table->index(['submission_type', 'is_active'], 'idx_email_templates_type_active');
            });
        }

        if (! Schema::hasTable('email_submissions')) {
            Schema::create('email_submissions', function (Blueprint $table) {
                $table->id();
                $table->enum('submission_type', ['perpanjangan', 'new_hire']);
                $table->string('applicant_name', 120);
                $table->string('company_name', 190)->nullable();
                $table->string('reference_no', 80)->nullable();
                $table->string('recipient_to', 500);
                $table->string('recipient_cc', 500)->nullable();
                $table->string('recipient_bcc', 500)->nullable();
                $table->foreignId('template_id')->nullable()->constrained('email_submission_templates')->nullOnDelete();
                $table->string('email_subject', 190);
                $table->longText('email_body');
                $table->enum('delivery_channel', ['gmail', 'manual'])->default('gmail');
                $table->enum('status', ['draft', 'sent', 'failed'])->default('draft');
                $table->timestamp('sent_at')->nullable();
                $table->text('last_error')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();

                $table->index(['status', 'created_at'], 'idx_email_submissions_status_created');
                $table->index(['submission_type', 'created_at'], 'idx_email_submissions_type_created');
            });
        }

        if (! Schema::hasTable('email_submission_attachments')) {
            Schema::create('email_submission_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('email_submissions')->cascadeOnDelete();
                $table->enum('doc_type', ['sim', 'ktp', 'fu', 'mcu', 'other']);
                $table->string('original_filename', 255);
                $table->string('stored_path', 500);
                $table->string('mime_type', 120);
                $table->unsignedBigInteger('file_size')->default(0);
                $table->char('checksum_sha256', 64);
                $table->timestamp('created_at')->useCurrent();

                $table->index(['submission_id', 'doc_type'], 'idx_email_attach_submission_doctype');
            });
        }

        $now = now();

        DB::table('email_submission_templates')->upsert([
            [
                'template_code' => 'simper-perpanjangan-default',
                'template_name' => 'SIMPER Perpanjangan - Standar',
                'submission_type' => 'perpanjangan',
                'recipient_cc' => null,
                'recipient_bcc' => null,
                'subject_template' => 'Pengajuan Perpanjangan SIMPER - {{applicant_name}} - {{company_name}}',
                'body_template' => "Yth. Tim SAPKON,\n\nMohon proses pengajuan perpanjangan SIMPER untuk data berikut:\n- Nama: {{applicant_name}}\n- Perusahaan: {{company_name}}\n- No Referensi: {{reference_no}}\n- Jenis Pengajuan: {{submission_type_label}}\n\nLampiran tertera pada email ini (SIM, KTP, FU, MCU, dan dokumen pendukung lain bila ada).\n\nTerima kasih.",
                'is_active' => 1,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'template_code' => 'simper-new-hire-default',
                'template_name' => 'SIMPER New Hire - Standar',
                'submission_type' => 'new_hire',
                'recipient_cc' => null,
                'recipient_bcc' => null,
                'subject_template' => 'Pengajuan SIMPER New Hire - {{applicant_name}} - {{company_name}}',
                'body_template' => "Yth. Tim SAPKON,\n\nMohon proses pengajuan SIMPER New Hire untuk data berikut:\n- Nama: {{applicant_name}}\n- Perusahaan: {{company_name}}\n- No Referensi: {{reference_no}}\n- Jenis Pengajuan: {{submission_type_label}}\n\nLampiran tertera pada email ini (SIM, KTP, FU, MCU, dan dokumen pendukung lain bila ada).\n\nTerima kasih.",
                'is_active' => 1,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['template_code'], ['template_name', 'submission_type', 'subject_template', 'body_template', 'is_active', 'updated_by', 'updated_at']);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_submission_attachments');
        Schema::dropIfExists('email_submissions');
        Schema::dropIfExists('email_submission_templates');
    }
};
