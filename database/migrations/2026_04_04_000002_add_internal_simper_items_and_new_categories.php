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
                'code' => 'pengajuan_nomer_lambung',
                'name' => 'Pengajuan Nomer Lambung',
                'description' => 'Kategori pengajuan nomor lambung kendaraan/alat operasional.',
                'sort_order' => 900,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'pengajuan_rambu',
                'name' => 'Pengajuan Rambu',
                'description' => 'Kategori pengajuan rambu keselamatan dan operasional area kerja.',
                'sort_order' => 910,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['code'], ['name', 'description', 'sort_order', 'is_active', 'updated_at']);

        DB::table('internal_company_groups')->upsert([
            [
                'group_name' => 'SIMPER Permit Internal',
                'sort_order' => 900,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['group_name'], ['sort_order', 'updated_at']);

        $groupId = (int) (DB::table('internal_company_groups')
            ->where('group_name', 'SIMPER Permit Internal')
            ->value('id') ?? 0);

        if ($groupId < 1) {
            return;
        }

        DB::table('internal_companies')->upsert([
            [
                'group_id' => $groupId,
                'code' => 'simper-permit-new-hire',
                'company_name' => 'Pengajuan simper & permit new hire',
                'is_manual_input_allowed' => 0,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group_id' => $groupId,
                'code' => 'simper-permit-perpanjangan',
                'company_name' => 'Perpanjangan simper & permit',
                'is_manual_input_allowed' => 0,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group_id' => $groupId,
                'code' => 'simper-permit-hilang-rusak',
                'company_name' => 'Pengajuan simper & permit hilang/Rusak',
                'is_manual_input_allowed' => 0,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['group_id', 'company_name'], ['code', 'is_manual_input_allowed', 'sort_order', 'updated_at']);
    }

    public function down(): void
    {
        $groupId = (int) (DB::table('internal_company_groups')
            ->where('group_name', 'SIMPER Permit Internal')
            ->value('id') ?? 0);

        if ($groupId > 0) {
            DB::table('internal_companies')
                ->where('group_id', $groupId)
                ->whereIn('company_name', [
                    'Pengajuan simper & permit new hire',
                    'Perpanjangan simper & permit',
                    'Pengajuan simper & permit hilang/Rusak',
                ])
                ->delete();

            $remainingRows = (int) DB::table('internal_companies')
                ->where('group_id', $groupId)
                ->count();

            if ($remainingRows === 0) {
                DB::table('internal_company_groups')->where('id', $groupId)->delete();
            }
        }

        DB::table('categories')
            ->whereIn('code', ['pengajuan_nomer_lambung', 'pengajuan_rambu'])
            ->whereNotExists(function ($query) {
                $query
                    ->select(DB::raw(1))
                    ->from('forms')
                    ->whereColumn('forms.category_id', 'categories.id');
            })
            ->delete();
    }
};
