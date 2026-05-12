<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSapkonToInternalCompanies extends Command
{
    protected $signature = 'sapkon:sync-internal-companies {--dry-run : Preview changes without writing to database}';

    protected $description = 'Sync companies from sapkon_companies into internal_companies (create vendor accounts without password)';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Fetching SAPKON companies with active form buckets...');

        $rows = DB::table('sapkon_companies as s')
            ->join('sapkon_form_buckets as b', 'b.sapkon_id', '=', 's.id')
            ->where('b.is_active', 1)
            ->select('s.sapkon_code', 's.sapkon_name')
            ->distinct()
            ->orderBy('s.sapkon_code')
            ->get();

        if ($rows->isEmpty()) {
            $this->info('No SAPKON companies with active buckets found.');

            return self::SUCCESS;
        }

        $now = now();

        $groupId = (int) DB::table('internal_company_groups')
            ->where('group_name', 'SAPKON Vendor')
            ->value('id');

        if ($groupId < 1) {
            if ($dryRun) {
                $this->line('[dry-run] Would create internal_company_groups: SAPKON Vendor');
                $groupId = 1;
            } else {
                DB::table('internal_company_groups')->insert([
                    'group_name' => 'SAPKON Vendor',
                    'sort_order' => 950,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $groupId = (int) DB::table('internal_company_groups')
                    ->where('group_name', 'SAPKON Vendor')
                    ->value('id');
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $sapkonCode = trim((string) ($row->sapkon_code ?? ''));
            $companyName = trim((string) ($row->sapkon_name ?? ''));

            if ($sapkonCode === '' || $companyName === '') {
                $skipped++;

                continue;
            }

            $existing = DB::table('internal_companies')
                ->where('code', $sapkonCode)
                ->orWhere(function ($query) use ($companyName): void {
                    $query->where('company_name', $companyName)->where('group_id', '!=', 58);
                })
                ->orderBy('id')
                ->first();

            if ($existing) {
                $needsUpdate =
                    (int) $existing->group_id !== $groupId ||
                    (string) ($existing->code ?? '') !== $sapkonCode ||
                    (string) ($existing->company_name ?? '') !== $companyName;

                if (! $needsUpdate) {
                    $skipped++;

                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] Would update internal_companies id={$existing->id} ({$companyName})");
                } else {
                    DB::table('internal_companies')
                        ->where('id', $existing->id)
                        ->update([
                            'group_id' => $groupId,
                            'code' => $sapkonCode,
                            'company_name' => $companyName,
                            'is_manual_input_allowed' => 0,
                            'updated_at' => $now,
                        ]);
                }

                $updated++;

                continue;
            }

            if ($dryRun) {
                $this->line("[dry-run] Would insert internal_companies ({$sapkonCode} - {$companyName})");
            } else {
                DB::table('internal_companies')->insert([
                    'group_id' => $groupId,
                    'code' => $sapkonCode,
                    'company_name' => $companyName,
                    'is_manual_input_allowed' => 0,
                    'sort_order' => 0,
                    'password_hash' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $created++;
        }

        $mode = $dryRun ? 'Dry-run summary' : 'Sync summary';
        $this->info($mode.': created='.$created.', updated='.$updated.', skipped='.$skipped.'.');

        return self::SUCCESS;
    }
}
