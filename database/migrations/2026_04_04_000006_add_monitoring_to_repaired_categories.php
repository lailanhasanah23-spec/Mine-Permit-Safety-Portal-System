<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_FORM_URL = 'https://docs.google.com/forms/d/e/1FAIpQLScDYKQg2eQlcmkGtl7ETOmaDlNrNYE8R_YGl1h-QscyXamBAA/viewform?usp=header';

    private const AUTO_NOTE_PREFIX = '[auto-default-monitoring]';

    /**
     * @var array<string, array{title: string, notes: string}>
     */
    private const TARGETS = [
        'INTERNAL' => [
            'title' => 'Monitoring Pengajuan Internal SIMPER & Permit',
            'notes' => self::AUTO_NOTE_PREFIX.' Monitoring untuk proses pengajuan internal SIMPER & Permit.',
        ],
        'pengajuan_nomer_lambung' => [
            'title' => 'Monitoring Pengajuan Nomer Lambung',
            'notes' => self::AUTO_NOTE_PREFIX.' Monitoring status pengajuan nomer lambung.',
        ],
        'pengajuan_rambu' => [
            'title' => 'Monitoring Pengajuan Rambu',
            'notes' => self::AUTO_NOTE_PREFIX.' Monitoring status pengajuan rambu operasional.',
        ],
        'pengajuan_commisioning' => [
            'title' => 'Monitoring Pengajuan Commisioning',
            'notes' => self::AUTO_NOTE_PREFIX.' Monitoring status pengajuan commisioning.',
        ],
    ];

    public function up(): void
    {
        $now = now();

        DB::transaction(function () use ($now): void {
            foreach (self::TARGETS as $categoryCode => $meta) {
                $categoryId = (int) (DB::table('categories')
                    ->where('code', $categoryCode)
                    ->where('is_active', 1)
                    ->value('id') ?? 0);

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

                $title = mb_substr((string) ($meta['title'] ?? 'Monitoring'), 0, 190);
                $notes = (string) ($meta['notes'] ?? self::AUTO_NOTE_PREFIX);
                $formUrl = $this->resolveCategoryFormUrl($categoryId);

                $existingTarget = DB::table('forms')
                    ->where('category_id', $categoryId)
                    ->where('purpose', 'monitoring')
                    ->where('title', $title)
                    ->first();

                if ($existingTarget !== null) {
                    $existingUrl = trim((string) ($existingTarget->form_url ?? ''));
                    $existingLinkScope = strtolower(trim((string) ($existingTarget->link_scope ?? '')));
                    $existingNotes = trim((string) ($existingTarget->notes ?? ''));

                    DB::table('forms')
                        ->where('id', $existingTarget->id)
                        ->update([
                            // Keep manual admin edits when migration is rerun.
                            'form_url' => $existingUrl !== '' ? $existingUrl : $formUrl,
                            'link_scope' => in_array($existingLinkScope, ['public', 'private'], true) ? $existingLinkScope : 'public',
                            'notes' => $existingNotes !== '' ? (string) $existingTarget->notes : $notes,
                            'effective_start' => $existingTarget->effective_start,
                            'effective_end' => $existingTarget->effective_end,
                            'is_active' => 1,
                            'updated_by' => $existingTarget->updated_by,
                            'updated_at' => $now,
                        ]);

                    continue;
                }

                DB::table('forms')->insert([
                    'category_id' => $categoryId,
                    'title' => $title,
                    'purpose' => 'monitoring',
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
        });
    }

    public function down(): void
    {
        DB::table('forms')
            ->where('purpose', 'monitoring')
            ->where('notes', 'like', self::AUTO_NOTE_PREFIX.'%')
            ->delete();
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
