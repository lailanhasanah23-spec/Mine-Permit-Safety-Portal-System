<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('categories')->upsert([
            [
                'code' => 'pengajuan_commisioning',
                'name' => 'Pengajuan Commisioning',
                'description' => 'Kategori pengajuan untuk proses commisioning operasional.',
                'sort_order' => 920,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code'], ['name', 'description', 'sort_order', 'is_active', 'updated_at']);
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('code', 'pengajuan_commisioning')
            ->whereNotExists(function ($query) {
                $query
                    ->select(DB::raw(1))
                    ->from('forms')
                    ->whereColumn('forms.category_id', 'categories.id');
            })
            ->delete();
    }
};
