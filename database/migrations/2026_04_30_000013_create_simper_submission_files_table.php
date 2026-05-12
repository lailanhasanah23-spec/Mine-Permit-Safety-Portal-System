<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('simper_submission_files')) {
            Schema::create('simper_submission_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('simper_submissions')->cascadeOnDelete();
                $table->enum('uploader_role', ['hrga', 'tod']);
                $table->string('file_type', 100);
                $table->string('file_path', 500);
                $table->string('file_name', 255);
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('simper_submission_files');
    }
};
