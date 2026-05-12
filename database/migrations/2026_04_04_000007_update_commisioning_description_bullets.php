<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CATEGORY_CODE = 'pengajuan_commisioning';

    private const CATEGORY_DESC_OLD = 'Kategori pengajuan untuk proses commisioning operasional. Pastikan unit bersih. Datang tepat waktu.';

    private const CATEGORY_DESC_NEW = "Kategori pengajuan untuk proses commisioning operasional.\n*Pastikan unit bersih\n*Datang tepat waktu";

    private const FORM_NOTES_OLD = "[auto-default] Pastikan unit bersih.\nDatang tepat waktu.";

    private const FORM_NOTES_NEW = "[auto-default]\n*Pastikan unit bersih\n*Datang tepat waktu";

    public function up(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            DB::table('categories')
                ->where('code', self::CATEGORY_CODE)
                ->update([
                    'description' => self::CATEGORY_DESC_NEW,
                    'updated_at' => $now,
                ]);

            $categoryId = (int) (DB::table('categories')
                ->where('code', self::CATEGORY_CODE)
                ->value('id') ?? 0);

            if ($categoryId > 0) {
                DB::table('forms')
                    ->where('category_id', $categoryId)
                    ->where('purpose', 'pengajuan')
                    ->where('title', 'Pengajuan Commisioning')
                    ->update([
                        'notes' => self::FORM_NOTES_NEW,
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    public function down(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            DB::table('categories')
                ->where('code', self::CATEGORY_CODE)
                ->where('description', self::CATEGORY_DESC_NEW)
                ->update([
                    'description' => self::CATEGORY_DESC_OLD,
                    'updated_at' => $now,
                ]);

            $categoryId = (int) (DB::table('categories')
                ->where('code', self::CATEGORY_CODE)
                ->value('id') ?? 0);

            if ($categoryId > 0) {
                DB::table('forms')
                    ->where('category_id', $categoryId)
                    ->where('purpose', 'pengajuan')
                    ->where('title', 'Pengajuan Commisioning')
                    ->where('notes', self::FORM_NOTES_NEW)
                    ->update([
                        'notes' => self::FORM_NOTES_OLD,
                        'updated_at' => $now,
                    ]);
            }
        });
    }
};
