<?php

declare(strict_types=1);

namespace App\Support\Monitoring;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class SpreadsheetChecklistService
{
    /**
     * @var string[]
     */
    private const CHECKED_HEADERS = [
        'checked',
        'is_checked',
        'checklist',
        'sudah_cek',
        'sudah_check',
        'done',
        'selesai',
        'valid',
        'approved',
        'approve',
    ];

    /**
     * @var string[]
     */
    private const STATUS_HEADERS = [
        'status',
        'monitoring_status',
        'status_monitoring',
        'progress',
    ];

    /**
     * @var string[]
     */
    private const LABEL_HEADERS = [
        'nama',
        'name',
        'unit',
        'perusahaan',
        'company',
        'submission_id',
        'id_pengajuan',
        'nomor_lambung',
        'no_lambung',
        'title',
        'judul',
    ];

    /**
     * @var string[]
     */
    private const TRUTHY_VALUES = [
        '1',
        'true',
        'yes',
        'ya',
        'y',
        'ok',
        'done',
        'selesai',
        'checked',
        'check',
        'valid',
        'approved',
        'complete',
        'completed',
    ];

    /**
     * @var string[]
     */
    private const COMPLETED_STATUS_VALUES = [
        'done',
        'selesai',
        'checked',
        'completed',
        'complete',
        'approved',
        'valid',
        'ok',
        'closed',
    ];

    public function summarizeForCategoryCode(string $categoryCode): array
    {
        $safeCategoryCode = $this->normalizeCategoryCode($categoryCode);
        $extensions = $this->allowedExtensions();
        $baseDir = $this->baseDirectory();

        $expectedFiles = [];
        foreach ($extensions as $ext) {
            $expectedFiles[] = $safeCategoryCode.'.'.$ext;
        }

        if ($safeCategoryCode === '') {
            return [
                'has_file' => false,
                'error' => 'Kode kategori tidak valid untuk pencarian spreadsheet.',
                'total_rows' => 0,
                'checked_rows' => 0,
                'pending_rows' => 0,
                'completion_percentage' => 0,
                'sample_rows' => [],
                'relative_path' => null,
                'file_name' => null,
                'last_modified_at' => null,
                'expected_files' => $expectedFiles,
            ];
        }

        $filePath = $this->resolveSpreadsheetPath($safeCategoryCode, $extensions, $baseDir);
        if ($filePath === null) {
            return [
                'has_file' => false,
                'error' => null,
                'total_rows' => 0,
                'checked_rows' => 0,
                'pending_rows' => 0,
                'completion_percentage' => 0,
                'sample_rows' => [],
                'relative_path' => null,
                'file_name' => null,
                'last_modified_at' => null,
                'expected_files' => $expectedFiles,
            ];
        }

        try {
            [$rows, $headers] = $this->readSpreadsheetRows($filePath);
        } catch (Throwable $e) {
            return [
                'has_file' => true,
                'error' => 'Spreadsheet ditemukan tetapi gagal dibaca: '.$e->getMessage(),
                'total_rows' => 0,
                'checked_rows' => 0,
                'pending_rows' => 0,
                'completion_percentage' => 0,
                'sample_rows' => [],
                'relative_path' => $this->toWorkspaceRelativePath($filePath),
                'file_name' => basename($filePath),
                'last_modified_at' => $this->formatFileMtime($filePath),
                'expected_files' => $expectedFiles,
            ];
        }

        $totalRows = count($rows);
        $checkedRows = 0;
        $sampleRows = [];
        $sampleLimit = max(1, min((int) config('monitoring_spreadsheet.sample_limit', 8), 20));

        foreach ($rows as $index => $row) {
            $isChecked = $this->isChecklistChecked($row);
            if ($isChecked) {
                $checkedRows++;
            }

            if (count($sampleRows) < $sampleLimit) {
                $sampleRows[] = [
                    'label' => $this->resolveRowLabel($row, $index + 1),
                    'checked' => $isChecked,
                    'status' => $this->resolveRowStatus($row, $isChecked),
                ];
            }
        }

        $pendingRows = max(0, $totalRows - $checkedRows);
        $completion = $totalRows > 0 ? (int) round(($checkedRows / $totalRows) * 100) : 0;

        return [
            'has_file' => true,
            'error' => null,
            'total_rows' => $totalRows,
            'checked_rows' => $checkedRows,
            'pending_rows' => $pendingRows,
            'completion_percentage' => $completion,
            'sample_rows' => $sampleRows,
            'relative_path' => $this->toWorkspaceRelativePath($filePath),
            'file_name' => basename($filePath),
            'last_modified_at' => $this->formatFileMtime($filePath),
            'expected_files' => $expectedFiles,
            'headers' => $headers,
        ];
    }

    /**
     * @return array{0: array<int, array<string, string>>, 1: array<int, string>}
     */
    private function readSpreadsheetRows(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray(null, true, true, false);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if (! is_array($matrix) || ! $matrix) {
            return [[], []];
        }

        $headerRow = array_shift($matrix);
        if (! is_array($headerRow)) {
            return [[], []];
        }

        $headers = [];
        foreach ($headerRow as $index => $cellValue) {
            $headers[$index] = $this->normalizeHeader((string) $cellValue);
        }

        $rows = [];
        foreach ($matrix as $rawRow) {
            if (! is_array($rawRow)) {
                continue;
            }

            $assoc = [];
            $hasAnyValue = false;

            foreach ($headers as $index => $header) {
                $value = trim((string) ($rawRow[$index] ?? ''));
                if ($value !== '') {
                    $hasAnyValue = true;
                }

                if ($header === '') {
                    continue;
                }

                $assoc[$header] = $value;
            }

            if (! $hasAnyValue || ! $assoc) {
                continue;
            }

            $rows[] = $assoc;
        }

        return [$rows, array_values(array_filter($headers, static fn (string $value): bool => $value !== ''))];
    }

    private function isChecklistChecked(array $row): bool
    {
        foreach (self::CHECKED_HEADERS as $header) {
            if (! array_key_exists($header, $row)) {
                continue;
            }

            if ($this->isTruthy((string) $row[$header])) {
                return true;
            }
        }

        foreach (self::STATUS_HEADERS as $header) {
            if (! array_key_exists($header, $row)) {
                continue;
            }

            $normalized = strtolower(trim((string) $row[$header]));
            if (in_array($normalized, self::COMPLETED_STATUS_VALUES, true)) {
                return true;
            }
        }

        return false;
    }

    private function resolveRowStatus(array $row, bool $isChecked): string
    {
        foreach (self::STATUS_HEADERS as $header) {
            $value = trim((string) ($row[$header] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        foreach (self::CHECKED_HEADERS as $header) {
            $value = trim((string) ($row[$header] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return $isChecked ? 'Checked' : 'Pending';
    }

    private function resolveRowLabel(array $row, int $rowNumber): string
    {
        foreach (self::LABEL_HEADERS as $header) {
            $value = trim((string) ($row[$header] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return 'Baris '.$rowNumber;
    }

    private function isTruthy(string $value): bool
    {
        return in_array(strtolower(trim($value)), self::TRUTHY_VALUES, true);
    }

    private function normalizeHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = preg_replace('/\s+/u', '_', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^a-z0-9_]+/u', '', $normalized) ?? $normalized;

        return trim((string) $normalized, '_');
    }

    private function normalizeCategoryCode(string $categoryCode): string
    {
        $normalized = strtolower(trim($categoryCode));
        $normalized = preg_replace('/[^a-z0-9_-]+/u', '_', $normalized) ?? $normalized;
        $normalized = trim((string) $normalized, '_');

        return mb_substr($normalized, 0, 80);
    }

    /**
     * @param  string[]  $extensions
     */
    private function resolveSpreadsheetPath(string $categoryCode, array $extensions, string $baseDir): ?string
    {
        foreach ($extensions as $extension) {
            $candidate = $baseDir.DIRECTORY_SEPARATOR.$categoryCode.'.'.$extension;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function allowedExtensions(): array
    {
        $configured = config('monitoring_spreadsheet.extensions', ['xlsx', 'xls', 'csv']);
        if (! is_array($configured)) {
            return ['xlsx', 'xls', 'csv'];
        }

        $extensions = [];
        foreach ($configured as $item) {
            $ext = strtolower(trim((string) $item));
            $ext = preg_replace('/[^a-z0-9]+/u', '', $ext) ?? '';
            if ($ext === '') {
                continue;
            }
            $extensions[] = $ext;
        }

        return $extensions ?: ['xlsx', 'xls', 'csv'];
    }

    private function baseDirectory(): string
    {
        $baseDir = (string) config('monitoring_spreadsheet.base_dir', storage_path('app/private/monitoring'));
        $baseDir = trim($baseDir);

        if ($baseDir === '') {
            $baseDir = storage_path('app/private/monitoring');
        }

        return rtrim($baseDir, DIRECTORY_SEPARATOR);
    }

    private function formatFileMtime(string $path): ?string
    {
        $mtime = @filemtime($path);
        if (! is_int($mtime) || $mtime < 1) {
            return null;
        }

        return date('Y-m-d H:i:s', $mtime);
    }

    private function toWorkspaceRelativePath(string $absolutePath): string
    {
        $normalizedPath = str_replace('\\', '/', $absolutePath);
        $normalizedBase = str_replace('\\', '/', base_path());
        $normalizedBase = rtrim($normalizedBase, '/');

        if (stripos($normalizedPath, $normalizedBase.'/') === 0) {
            return substr($normalizedPath, strlen($normalizedBase) + 1);
        }

        return $normalizedPath;
    }
}
