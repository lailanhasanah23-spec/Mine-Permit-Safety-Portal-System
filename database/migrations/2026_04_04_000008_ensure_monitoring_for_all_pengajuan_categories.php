<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_FORM_URL = 'https://docs.google.com/forms/d/e/1FAIpQLScDYKQg2eQlcmkGtl7ETOmaDlNrNYE8R_YGl1h-QscyXamBAA/viewform?usp=header';

    private const AUTO_NOTE_PREFIX = '[auto-monitoring-all]';

    public function up(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            $categories = DB::table('categories')
                ->select('id', 'name')
                ->where('is_active', 1)
                ->whereExists(function ($query): void {
                    $query->select(DB::raw(1))
                        ->from('forms')
                        ->whereColumn('forms.category_id', 'categories.id')
                        ->where('forms.purpose', 'pengajuan')
                        ->where('forms.is_active', 1);
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            foreach ($categories as $category) {
                $categoryId = (int) ($category->id ?? 0);
                if ($categoryId < 1) {
                    continue;
                }

                $hasActiveMonitoring = (int) DB::table('forms')
                    ->where('category_id', $categoryId)
                    ->where('purpose', 'monitoring')
                    ->where('is_active', 1)
                    ->count();

                if ($hasActiveMonitoring > 0) {
                    continue;
                }

                $baseTitle = $this->buildMonitoringTitle((string) ($category->name ?? 'Kategori'));
                $title = $this->resolveUniqueTitle($categoryId, $baseTitle);

                DB::table('forms')->insert([
                    'category_id' => $categoryId,
                    'title' => $title,
                    'purpose' => 'monitoring',
                    'form_url' => $this->resolveCategoryFormUrl($categoryId),
                    'link_scope' => 'public',
                    'notes' => self::AUTO_NOTE_PREFIX.' Monitoring otomatis agar checklist pengajuan per kategori aktif.',
                    'effective_start' => null,
                    'effective_end' => null,
                    'is_active' => 1,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('forms')
            ->where('purpose', 'monitoring')
            ->where('notes', 'like', self::AUTO_NOTE_PREFIX.'%')
            ->delete();
    }

    private function buildMonitoringTitle(string $categoryName): string
    {
        $normalizedName = trim((string) (preg_replace('/\s+/u', ' ', $categoryName) ?? $categoryName));
        if ($normalizedName === '') {
            $normalizedName = 'Kategori';
        }

        $withoutPengajuanPrefix = preg_replace('/^pengajuan\s+/iu', '', $normalizedName);
        if (is_string($withoutPengajuanPrefix) && trim($withoutPengajuanPrefix) !== '') {
            $normalizedName = trim($withoutPengajuanPrefix);
        }

        $title = stripos($normalizedName, 'monitoring') === 0
            ? $normalizedName
            : 'Monitoring '.$normalizedName;

        return mb_substr($title, 0, 190);
    }

    private function resolveUniqueTitle(int $categoryId, string $baseTitle): string
    {
        $candidate = mb_substr($baseTitle, 0, 190);
        $suffixNumber = 2;

        while (DB::table('forms')
            ->where('category_id', $categoryId)
            ->where('purpose', 'monitoring')
            ->where('title', $candidate)
            ->exists()) {
            $suffix = ' ('.$suffixNumber.')';
            $maxBaseLength = max(1, 190 - mb_strlen($suffix));
            $candidate = mb_substr($baseTitle, 0, $maxBaseLength).$suffix;
            $suffixNumber++;

            if ($suffixNumber > 99) {
                break;
            }
        }

        return $candidate;
    }

    private function resolveCategoryFormUrl(int $categoryId): string
    {
        $categoryMonitoringUrl = (string) (DB::table('forms')
            ->where('category_id', $categoryId)
            ->where('purpose', 'monitoring')
            ->where('is_active', 1)
            ->whereNotNull('form_url')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('form_url') ?? '');

        if (trim($categoryMonitoringUrl) !== '') {
            return $categoryMonitoringUrl;
        }

        $categoryPengajuanUrl = (string) (DB::table('forms')
            ->where('category_id', $categoryId)
            ->where('purpose', 'pengajuan')
            ->where('is_active', 1)
            ->whereNotNull('form_url')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('form_url') ?? '');

        if (trim($categoryPengajuanUrl) !== '') {
            return $categoryPengajuanUrl;
        }

        $globalMonitoringUrl = (string) (DB::table('forms')
            ->where('purpose', 'monitoring')
            ->where('is_active', 1)
            ->whereNotNull('form_url')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('form_url') ?? '');

        if (trim($globalMonitoringUrl) !== '') {
            return $globalMonitoringUrl;
        }

        $globalAnyUrl = (string) (DB::table('forms')
            ->where('is_active', 1)
            ->whereNotNull('form_url')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('form_url') ?? '');

        return trim($globalAnyUrl) !== '' ? $globalAnyUrl : self::DEFAULT_FORM_URL;
    }
};
