<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('forms')) {
            Schema::create('forms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('categories');
                $table->string('title', 190);
                $table->enum('purpose', ['pengajuan', 'monitoring']);
                $table->string('form_url', 500);
                $table->enum('link_scope', ['public', 'private'])->default('public');
                $table->text('notes')->nullable();
                $table->date('effective_start')->nullable();
                $table->date('effective_end')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();

                $table->unique(['category_id', 'title', 'purpose'], 'uniq_forms_category_title_purpose');
                $table->index(['category_id', 'is_active'], 'idx_forms_category_active');
                $table->index('link_scope', 'idx_forms_link_scope');
            });
        }

        if (! Schema::hasTable('internal_company_groups')) {
            Schema::create('internal_company_groups', function (Blueprint $table) {
                $table->id();
                $table->string('group_name', 100)->unique();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('internal_companies')) {
            Schema::create('internal_companies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('internal_company_groups');
                $table->string('code', 50)->nullable();
                $table->string('company_name', 190);
                $table->boolean('is_manual_input_allowed')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['group_id', 'company_name'], 'uniq_internal_companies_group_name');
            });
        }

        if (! Schema::hasTable('required_documents')) {
            Schema::create('required_documents', function (Blueprint $table) {
                $table->id();
                $table->enum('doc_scope', ['simper', 'mine_permit']);
                $table->string('doc_name', 190);
                $table->boolean('is_conditional')->default(false);
                $table->string('condition_notes', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['doc_scope', 'doc_name'], 'uniq_doc_scope_name');
            });
        }

        if (! Schema::hasTable('sapkon_companies')) {
            Schema::create('sapkon_companies', function (Blueprint $table) {
                $table->id();
                $table->string('sapkon_code', 20)->unique();
                $table->string('sapkon_name', 190);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sapkon_form_buckets')) {
            Schema::create('sapkon_form_buckets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sapkon_id')->constrained('sapkon_companies');
                $table->enum('form_type', ['simper', 'mine_permit']);
                $table->string('form_url', 500);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['sapkon_id', 'form_type'], 'uniq_sapkon_form_type');
            });
        }

        if (! Schema::hasTable('auth_login_attempts')) {
            Schema::create('auth_login_attempts', function (Blueprint $table) {
                $table->id();
                $table->char('identifier_hash', 64);
                $table->string('ip_address', 45);
                $table->unsignedInteger('attempt_count')->default(0);
                $table->dateTime('locked_until')->nullable();
                $table->timestamp('last_attempt_at')->useCurrent()->useCurrentOnUpdate();
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['identifier_hash', 'ip_address'], 'uniq_identifier_ip');
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_user_id')->nullable()->constrained('users');
                $table->string('action', 120);
                $table->string('entity_type', 120);
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->json('before_state')->nullable();
                $table->json('after_state')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('auth_login_attempts');
        Schema::dropIfExists('sapkon_form_buckets');
        Schema::dropIfExists('sapkon_companies');
        Schema::dropIfExists('required_documents');
        Schema::dropIfExists('internal_companies');
        Schema::dropIfExists('internal_company_groups');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('categories');
    }
};
