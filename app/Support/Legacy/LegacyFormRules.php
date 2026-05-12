<?php

namespace App\Support\Legacy;

use DateTimeImmutable;

class LegacyFormRules
{
    /**
     * Extract a Google Drive Folder ID from either a raw ID or a full Drive URL.
     * Accepts formats like:
     *   - 1aBcDeFgHiJkLmNoPqRsTuVwXyZ123456 (raw ID)
     *   - https://drive.google.com/drive/folders/1aBcD... (folder URL)
     *   - https://drive.google.com/drive/u/0/folders/1aBcD... (user-scoped URL)
     */
    public static function extractGdriveFolderId(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        // If it looks like a URL, try to extract the folder ID from the path.
        if (str_starts_with($raw, 'http')) {
            if (preg_match('#/folders/([A-Za-z0-9_\-]{10,})#', $raw, $matches)) {
                return $matches[1];
            }

            // Could not parse a folder ID from the URL.
            return '';
        }

        // Validate raw ID: only alphanumeric, underscore, hyphen; length 10-256.
        if (preg_match('/^[A-Za-z0-9_\-]{10,256}$/', $raw)) {
            return $raw;
        }

        return '';
    }

    public static function normalizeFormTitle(string $title): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($title));

        return $normalized !== null ? $normalized : trim($title);
    }

    public static function canonicalizeFormUrl(string $url): string
    {
        $rawUrl = self::normalizeRawGoogleFormInput($url);
        if ($rawUrl === '') {
            return '';
        }

        $parts = parse_url($rawUrl);
        if (! is_array($parts)) {
            return '';
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = self::normalizeGoogleFormHost((string) ($parts['host'] ?? ''));
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $path = (string) ($parts['path'] ?? '/');

        if ($scheme === '' || $host === '') {
            return '';
        }

        if (! in_array($scheme, ['https', 'http'], true)) {
            return '';
        }

        if ($scheme === 'http' && self::isSupportedGoogleFormHost($host)) {
            $scheme = 'https';
        }

        if ($scheme !== 'https') {
            return '';
        }

        if ($host === 'docs.google.com' || str_ends_with($host, '.docs.google.com')) {
            $path = preg_replace('#/+#', '/', $path) ?? $path;

            if (preg_match('#^(/forms(?:/u/\d+)?/d(?:/e)?/[^/]+)/(?:viewform|formresponse|edit|prefill)(?:/.*)?$#i', $path, $matches)) {
                $path = $matches[1].'/viewform';
            } elseif (preg_match('#^(/forms(?:/u/\d+)?/d(?:/e)?/[^/]+)/?$#i', $path, $matches)) {
                $path = $matches[1].'/viewform';
            }
        }

        $normalizedUrl = $scheme.'://'.$host;
        if ($port !== null && ! ($scheme === 'https' && $port === 443) && ! ($scheme === 'http' && $port === 80)) {
            $normalizedUrl .= ':'.$port;
        }

        $normalizedUrl .= $path !== '' ? $path : '/';

        $sanitizedQuery = self::sanitizeGoogleFormQueryString(isset($parts['query']) ? (string) $parts['query'] : '');
        if ($sanitizedQuery !== '') {
            $normalizedUrl .= '?'.$sanitizedQuery;
        }

        return $normalizedUrl;
    }

    public static function isAllowedFormUrl(string $url, string $linkScope = 'public', string $purpose = 'pengajuan'): bool
    {
        $normalizedUrl = self::canonicalizeFormUrl($url);
        if ($normalizedUrl === '' || ! filter_var($normalizedUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($normalizedUrl);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = self::normalizeGoogleFormHost((string) ($parts['host'] ?? ''));
        $normalizedLinkScope = strtolower(trim($linkScope));
        $normalizedPurpose = strtolower(trim($purpose));

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        if (! self::isSupportedGoogleFormHost($host)) {
            return false;
        }

        if ($host === 'forms.gle' || str_ends_with($host, '.forms.gle')) {
            if ($normalizedLinkScope === 'private') {
                return false;
            }

            $path = trim((string) ($parts['path'] ?? ''), '/');

            return $path !== '' && ! str_contains($path, 'create');
        }

        if (! ($host === 'docs.google.com' || str_ends_with($host, '.docs.google.com'))) {
            return false;
        }

        $path = strtolower((string) ($parts['path'] ?? ''));
        $path = preg_replace('#/+#', '/', $path) ?? $path;

        if (str_contains($path, '/settings')) {
            return false;
        }

        // Monitoring link can point to spreadsheet tracker.
        if ($normalizedPurpose === 'monitoring' && (bool) preg_match('#^/spreadsheets/d/[^/]+(?:/(?:edit|view))?/?$#', $path)) {
            return true;
        }

        if ($normalizedPurpose === 'monitoring' && $normalizedLinkScope === 'private') {
            return (bool) preg_match('#^/forms(?:/u/\d+)?/d(?:/e)?/[^/]+/(?:viewform|viewanalytics|edit)/?$#', $path);
        }

        if (str_contains($path, '/viewanalytics')) {
            return false;
        }

        return (bool) preg_match('#^/forms(?:/u/\d+)?/d(?:/e)?/[^/]+/viewform/?$#', $path);
    }

    private static function normalizeRawGoogleFormInput(string $url): string
    {
        $rawUrl = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($rawUrl === '') {
            return '';
        }

        // Remove hidden copy-paste characters that often break URL parsing.
        $rawUrl = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{2060}]/u', '', $rawUrl) ?? $rawUrl;
        $rawUrl = preg_replace('/[\r\n\t]+/u', '', $rawUrl) ?? $rawUrl;

        if (str_starts_with($rawUrl, '//')) {
            $rawUrl = 'https:'.$rawUrl;
        }

        if (! preg_match('#^[a-z][a-z0-9+\-.]*://#i', $rawUrl)) {
            $lowerRawUrl = strtolower($rawUrl);
            $googlePrefixes = [
                'docs.google.com/',
                'www.docs.google.com/',
                'forms.google.com/',
                'www.forms.google.com/',
                'forms.gle/',
                'www.forms.gle/',
            ];

            foreach ($googlePrefixes as $prefix) {
                if (str_starts_with($lowerRawUrl, $prefix)) {
                    $rawUrl = 'https://'.ltrim($rawUrl, '/');
                    break;
                }
            }
        }

        return $rawUrl;
    }

    private static function normalizeGoogleFormHost(string $host): string
    {
        $normalizedHost = strtolower(trim($host));
        if ($normalizedHost === '') {
            return '';
        }

        if (str_starts_with($normalizedHost, 'www.')) {
            $normalizedHost = substr($normalizedHost, 4);
        }

        if ($normalizedHost === 'forms.google.com') {
            return 'docs.google.com';
        }

        return $normalizedHost;
    }

    private static function isSupportedGoogleFormHost(string $host): bool
    {
        return $host === 'docs.google.com'
            || str_ends_with($host, '.docs.google.com')
            || $host === 'forms.gle'
            || str_ends_with($host, '.forms.gle');
    }

    private static function sanitizeGoogleFormQueryString(string $query): string
    {
        $query = trim($query);
        if ($query === '') {
            return '';
        }

        $filteredPairs = [];
        $pairs = explode('&', $query);
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }

            $keyValue = explode('=', $pair, 2);
            $rawKey = $keyValue[0] ?? '';
            $decodedKey = strtolower(rawurldecode(str_replace('+', '%20', $rawKey)));

            if (
                str_starts_with($decodedKey, 'utm_')
                || in_array($decodedKey, ['fbclid', 'gclid', 'igshid', 'source', 'src'], true)
            ) {
                continue;
            }

            $filteredPairs[] = $pair;
        }

        return implode('&', $filteredPairs);
    }

    private static function validateEffectiveDate(string $value, string $label, array &$errors): ?string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $normalized);
        $dateErrors = DateTimeImmutable::getLastErrors();
        $hasParseError = is_array($dateErrors) && (($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0);

        if (! $date || $date->format('Y-m-d') !== $normalized || $hasParseError) {
            $errors[] = $label.' tidak valid (format wajib YYYY-MM-DD).';

            return null;
        }

        return $date->format('Y-m-d');
    }

    public static function validateCreate(array $input): array
    {
        $errors = [];

        $categoryId = (int) ($input['category_id'] ?? 0);
        $title = self::normalizeFormTitle((string) ($input['title'] ?? ''));
        $purpose = (string) ($input['purpose'] ?? 'pengajuan');
        $linkScope = strtolower(trim((string) ($input['link_scope'] ?? 'public')));
        $formUrl = self::canonicalizeFormUrl((string) ($input['form_url'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));
        $gdriveFolderId = self::extractGdriveFolderId((string) ($input['gdrive_folder_id'] ?? ''));
        $effectiveStart = self::validateEffectiveDate((string) ($input['effective_start'] ?? ''), 'Tanggal mulai aktif', $errors);
        $effectiveEnd = self::validateEffectiveDate((string) ($input['effective_end'] ?? ''), 'Tanggal akhir aktif', $errors);

        // Validate folder ID if provided but could not be parsed.
        $rawGdriveInput = trim((string) ($input['gdrive_folder_id'] ?? ''));
        if ($rawGdriveInput !== '' && $gdriveFolderId === '') {
            $errors[] = 'Link atau ID Folder Google Drive tidak valid. Tempel URL folder Drive atau ID folder secara langsung.';
        }

        if ($categoryId < 1) {
            $errors[] = 'Kategori wajib dipilih.';
        }

        if ($title === '') {
            $errors[] = 'Judul formulir wajib diisi.';
        }

        if (mb_strlen($title) > 190) {
            $errors[] = 'Judul formulir maksimal 190 karakter.';
        }

        if (! in_array($purpose, ['pengajuan', 'monitoring'], true)) {
            $errors[] = 'Tujuan formulir tidak valid.';
        }

        if (! in_array($linkScope, ['public', 'private'], true)) {
            $errors[] = 'Scope akses tautan tidak valid.';
        }

        if (! self::isAllowedFormUrl($formUrl, $linkScope, $purpose)) {
            if ($purpose === 'monitoring' && $linkScope === 'private') {
                $errors[] = 'URL monitoring private wajib menggunakan docs.google.com ... /viewform, /viewanalytics, atau spreadsheet /edit berbasis HTTPS (forms.gle tidak didukung).';
            } elseif ($purpose === 'monitoring') {
                $errors[] = 'URL monitoring harus menggunakan docs.google.com ... /viewform atau spreadsheet /edit berbasis HTTPS.';
            } elseif ($linkScope === 'private') {
                $errors[] = 'URL formulir private wajib menggunakan link docs.google.com ... /viewform (bukan forms.gle).';
            } else {
                $errors[] = 'URL formulir harus berupa link publik Google Form (viewform) berbasis HTTPS.';
            }
        }

        if (mb_strlen($formUrl) > 500) {
            $errors[] = 'URL formulir terlalu panjang (maksimal 500 karakter).';
        }

        if (mb_strlen($notes) > 5000) {
            $errors[] = 'Catatan terlalu panjang (maksimal 5000 karakter).';
        }

        if ($effectiveStart !== null && $effectiveEnd !== null && $effectiveStart > $effectiveEnd) {
            $errors[] = 'Rentang tanggal tidak valid: tanggal mulai aktif harus lebih kecil atau sama dengan tanggal akhir aktif.';
        }

        return [
            'ok' => count($errors) === 0,
            'errors' => $errors,
            'data' => [
                'category_id' => $categoryId,
                'title' => $title,
                'purpose' => $purpose,
                'link_scope' => $linkScope,
                'form_url' => $formUrl,
                'gdrive_folder_id' => $gdriveFolderId !== '' ? $gdriveFolderId : null,
                'notes' => $notes,
                'effective_start' => $effectiveStart,
                'effective_end' => $effectiveEnd,
            ],
        ];
    }

    public static function validateUpdate(array $input): array
    {
        $result = self::validateCreate($input);
        $result['data']['is_active'] = isset($input['is_active']) ? 1 : 0;

        return $result;
    }
}
