<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('policy_documents')) {
            Schema::create('policy_documents', function (Blueprint $table) {
                $table->id();
                $table->string('code', 120)->unique();
                $table->string('title', 190);
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('policy_document_revisions')) {
            Schema::create('policy_document_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained('policy_documents')->cascadeOnDelete();
                $table->string('revision_label', 80);
                $table->string('original_filename', 255);
                $table->string('stored_path', 500);
                $table->string('mime_type', 120)->default('application/pdf');
                $table->unsignedBigInteger('file_size')->default(0);
                $table->char('checksum_sha256', 64);
                $table->text('notes')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users');
                $table->timestamp('created_at')->useCurrent();

                $table->index(['document_id', 'created_at'], 'idx_policy_doc_revisions_doc_created');
            });
        }

        $now = now();

        $documentCode = 'ktp-ohs-102-mine-permit-simper';
        $documentPayload = [
            'title' => 'KTP-OHS-102 Mine Permit & SIMPER',
            'description' => 'Dokumen standar operasional KTP-OHS-102 untuk tata kelola Mine Permit dan SIMPER. Gunakan versi revisi terbaru yang dipublikasikan.',
            'is_public' => 1,
            'updated_by' => null,
            'updated_at' => $now,
        ];

        $documentExists = DB::table('policy_documents')
            ->where('code', $documentCode)
            ->exists();

        if ($documentExists) {
            DB::table('policy_documents')
                ->where('code', $documentCode)
                ->update($documentPayload);
        } else {
            DB::table('policy_documents')->insert($documentPayload + [
                'code' => $documentCode,
                'created_by' => null,
                'created_at' => $now,
            ]);
        }

        $documentId = (int) (DB::table('policy_documents')
            ->where('code', $documentCode)
            ->value('id') ?? 0);

        $sourcePath = base_path('KTP-OHS-102 MINE PERMIT & SIMPER_Rev6.pdf');

        if ($documentId > 0 && is_file($sourcePath)) {
            $storedPath = 'private/policy-documents/ktp-ohs-102-mine-permit-simper/'.date('YmdHis').'-ktp-ohs-102-rev6.pdf';

            $rawFile = file_get_contents($sourcePath);
            if ($rawFile !== false) {
                $existingRevisionId = (int) (DB::table('policy_document_revisions')
                    ->where('document_id', $documentId)
                    ->where('revision_label', 'Rev6')
                    ->value('id') ?? 0);

                if ($existingRevisionId < 1) {
                    Storage::disk('local')->put($storedPath, $rawFile);

                    DB::table('policy_document_revisions')->insert([
                        'document_id' => $documentId,
                        'revision_label' => 'Rev6',
                        'original_filename' => 'KTP-OHS-102 MINE PERMIT & SIMPER_Rev6.pdf',
                        'stored_path' => $storedPath,
                        'mime_type' => 'application/pdf',
                        'file_size' => (int) (filesize($sourcePath) ?: 0),
                        'checksum_sha256' => (string) (hash_file('sha256', $sourcePath) ?: str_repeat('0', 64)),
                        'notes' => 'Inisialisasi otomatis dari file awal proyek.',
                        'uploaded_by' => null,
                        'created_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_document_revisions');
        Schema::dropIfExists('policy_documents');
    }
};
