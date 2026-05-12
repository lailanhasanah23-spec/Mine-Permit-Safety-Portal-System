<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const COMMISIONING_CODE = 'pengajuan_commisioning';

    private const COMMISIONING_DESC_OLD = 'Kategori pengajuan untuk proses commisioning operasional.';

    private const COMMISIONING_DESC_NEW = 'Kategori pengajuan untuk proses commisioning operasional. Pastikan unit bersih. Datang tepat waktu.';

    private const DEFAULT_NOTE_PREFIX = '[auto-default]';

    private const DEFAULT_FORM_URL = 'https://docs.google.com/forms/d/e/1FAIpQLScDYKQg2eQlcmkGtl7ETOmaDlNrNYE8R_YGl1h-QscyXamBAA/viewform?usp=header';

    public function up(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            DB::table('categories')->upsert([
                [
                    'code' => self::COMMISIONING_CODE,
                    'name' => 'Pengajuan Commisioning',
                    'description' => self::COMMISIONING_DESC_NEW,
                    'sort_order' => 920,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ], ['code'], ['name', 'description', 'sort_order', 'is_active', 'updated_at']);

            DB::table('internal_company_groups')->updateOrInsert(
                ['group_name' => 'SIMPER Permit Internal'],
                [
                    'sort_order' => 900,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $groupId = (int) (DB::table('internal_company_groups')
                ->where('group_name', 'SIMPER Permit Internal')
                ->value('id') ?? 0);

            if ($groupId > 0) {
                $this->ensureInternalCompany($groupId, 'simper-permit-new-hire', 'Pengajuan SIMPER & Permit', 10, $now);
                $this->ensureInternalCompany($groupId, 'simper-permit-perpanjangan', 'Perpanjangan SIMPER & Permit', 20, $now);
                $this->ensureInternalCompany($groupId, 'simper-permit-hilang-rusak', 'Pengajuan Rusak/Hilang', 30, $now);
            }

            $formUrl = $this->resolveDefaultFormUrl();
            $internalCategoryId = $this->findCategoryIdByCode('INTERNAL');
            $commisioningCategoryId = $this->findCategoryIdByCode(self::COMMISIONING_CODE);

            if ($internalCategoryId > 0) {
                $this->ensurePengajuanForm(
                    $internalCategoryId,
                    'Pengajuan SIMPER & Permit',
                    $formUrl,
                    self::DEFAULT_NOTE_PREFIX.' Pengajuan internal SIMPER & Permit.',
                    $now
                );
                $this->ensurePengajuanForm(
                    $internalCategoryId,
                    'Perpanjangan SIMPER & Permit',
                    $formUrl,
                    self::DEFAULT_NOTE_PREFIX.' Perpanjangan internal SIMPER & Permit.',
                    $now
                );
                $this->ensurePengajuanForm(
                    $internalCategoryId,
                    'Pengajuan Rusak/Hilang',
                    $formUrl,
                    self::DEFAULT_NOTE_PREFIX.' Pengajuan internal SIMPER & Permit rusak atau hilang.',
                    $now
                );
            }

            if ($commisioningCategoryId > 0) {
                $this->ensurePengajuanForm(
                    $commisioningCategoryId,
                    'Pengajuan Commisioning',
                    $formUrl,
                    self::DEFAULT_NOTE_PREFIX." Pastikan unit bersih.\nDatang tepat waktu.",
                    $now
                );
            }

            $activeCategories = DB::table('categories')
                ->select('id', 'code', 'name')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($activeCategories as $category) {
                $categoryId = (int) ($category->id ?? 0);
                if ($categoryId < 1) {
                    continue;
                }

                $activeFormsCount = (int) DB::table('forms')
                    ->where('category_id', $categoryId)
                    ->where('is_active', 1)
                    ->count();

                if ($activeFormsCount > 0) {
                    continue;
                }

                $title = $this->buildDefaultTitle((string) ($category->code ?? ''), (string) ($category->name ?? 'Formulir'));
                $note = self::DEFAULT_NOTE_PREFIX.' Form pengajuan default otomatis karena kategori ini belum memiliki pengajuan aktif.';

                $this->ensurePengajuanForm($categoryId, $title, $formUrl, $note, $now);
            }
        });
    }

    public function down(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            DB::table('forms')
                ->where('notes', 'like', self::DEFAULT_NOTE_PREFIX.'%')
                ->delete();

            $groupId = (int) (DB::table('internal_company_groups')
                ->where('group_name', 'SIMPER Permit Internal')
                ->value('id') ?? 0);

            if ($groupId > 0) {
                DB::table('internal_companies')
                    ->where('group_id', $groupId)
                    ->where('code', 'simper-permit-new-hire')
                    ->update([
                        'company_name' => 'Pengajuan simper & permit new hire',
                        'updated_at' => $now,
                    ]);

                DB::table('internal_companies')
                    ->where('group_id', $groupId)
                    ->where('code', 'simper-permit-perpanjangan')
                    ->update([
                        'company_name' => 'Perpanjangan simper & permit',
                        'updated_at' => $now,
                    ]);

                DB::table('internal_companies')
                    ->where('group_id', $groupId)
                    ->where('code', 'simper-permit-hilang-rusak')
                    ->update([
                        'company_name' => 'Pengajuan simper & permit hilang/Rusak',
                        'updated_at' => $now,
                    ]);
            }

            DB::table('categories')
                ->where('code', self::COMMISIONING_CODE)
                ->where('description', self::COMMISIONING_DESC_NEW)
                ->update([
                    'description' => self::COMMISIONING_DESC_OLD,
                    'updated_at' => $now,
                ]);
        });
    }

    private function findCategoryIdByCode(string $code): int
    {
        return (int) (DB::table('categories')->where('code', $code)->value('id') ?? 0);
    }

    private function resolveDefaultFormUrl(): string
    {
        $url = (string) (DB::table('forms')
            ->where('is_active', 1)
            ->whereNotNull('form_url')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('form_url') ?? '');

        return trim($url) !== '' ? $url : self::DEFAULT_FORM_URL;
    }

    private function ensureInternalCompany(int $groupId, string $code, string $companyName, int $sortOrder, $now): void
    {
        $existingByCode = DB::table('internal_companies')
            ->where('group_id', $groupId)
            ->where('code', $code)
            ->first();

        if ($existingByCode !== null) {
            DB::table('internal_companies')
                ->where('id', $existingByCode->id)
                ->update([
                    'company_name' => $companyName,
                    'is_manual_input_allowed' => 0,
                    'sort_order' => $sortOrder,
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('internal_companies')->updateOrInsert(
            [
                'group_id' => $groupId,
                'company_name' => $companyName,
            ],
            [
                'code' => $code,
                'is_manual_input_allowed' => 0,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function ensurePengajuanForm(int $categoryId, string $title, string $formUrl, string $notes, $now): void
    {
        $existing = DB::table('forms')
            ->where('category_id', $categoryId)
            ->where('title', $title)
            ->where('purpose', 'pengajuan')
            ->first();

        if ($existing !== null) {
            $existingUrl = trim((string) ($existing->form_url ?? ''));
            $existingLinkScope = strtolower(trim((string) ($existing->link_scope ?? '')));
            $existingNotes = trim((string) ($existing->notes ?? ''));

            DB::table('forms')
                ->where('id', $existing->id)
                ->update([
                    // Keep manual admin edits when migration is rerun.
                    'form_url' => $existingUrl !== '' ? $existingUrl : $formUrl,
                    'link_scope' => in_array($existingLinkScope, ['public', 'private'], true) ? $existingLinkScope : 'public',
                    'notes' => $existingNotes !== '' ? (string) $existing->notes : $notes,
                    'effective_start' => $existing->effective_start,
                    'effective_end' => $existing->effective_end,
                    'is_active' => (int) ($existing->is_active ?? 0) === 1 ? 1 : 0,
                    'updated_by' => $existing->updated_by,
                    'updated_at' => $now,
                ]);

            return;
        }

        DB::table('forms')->insert([
            'category_id' => $categoryId,
            'title' => $title,
            'purpose' => 'pengajuan',
            'form_url' => $formUrl,
            'link_scope' => 'public',
            'notes' => $notes,
            'effective_start' => null,
            'effective_end' => null,
            'is_active' => 1,
            'created_by' => null,
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function buildDefaultTitle(string $code, string $name): string
    {
        $normalizedCode = strtolower(trim($code));

        if ($normalizedCode === 'pengajuan_nomer_lambung') {
            return 'Pengajuan Nomer Lambung';
        }

        if ($normalizedCode === 'pengajuan_rambu') {
            return 'Pengajuan Rambu';
        }

        $normalizedName = trim($name);
        if ($normalizedName === '') {
            $normalizedName = 'Formulir';
        }

        $title = stripos($normalizedName, 'pengajuan') === 0
            ? $normalizedName
            : 'Pengajuan '.$normalizedName;

        return mb_substr($title, 0, 190);
    }
};
