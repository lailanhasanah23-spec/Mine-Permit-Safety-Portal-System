<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('simper_submissions')) {
            Schema::create('simper_submissions', function (Blueprint $table) {
                $table->id();
                $table->string('applicant_name', 190);
                $table->string('applicant_nik', 50);
                $table->string('company_name', 190);
                $table->string('simper_type', 100);
                $table->enum('status', ['pending_hrga', 'pending_tod', 'pending_she', 'approved', 'rejected'])->default('pending_hrga');
                $table->text('she_notes')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('simper_submissions');
    }
};
