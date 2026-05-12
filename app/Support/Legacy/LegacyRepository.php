<?php

namespace App\Support\Legacy;

use DateInterval;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOStatement;
use Throwable;

class LegacyRepository
{
    private static function pdo(): PDO
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    private static function fetchAll(PDOStatement $stmt): array
    {
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    private static function fetchOne(PDOStatement $stmt): ?array
    {
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public static function normalizeGoogleFormUrl(string $rawUrl, string $linkScope = 'public', string $purpose = 'pengajuan'): string
    {
        $normalizedUrl = LegacyFormRules::canonicalizeFormUrl($rawUrl);
        if ($normalizedUrl === '') {
            return '';
        }

        if (! LegacyFormRules::isAllowedFormUrl($normalizedUrl, $linkScope, $purpose)) {
            return '';
        }

        return $normalizedUrl;
    }

    public static function portalGetCategories(): array
    {
        $pdo = self::pdo();

        return self::fetchAll($pdo->query(
            'SELECT id, code, name, description FROM categories WHERE is_active = 1 ORDER BY sort_order, name'
        ));
    }

    public static function portalGetFormsByCategory(array $categories, bool $includeMonitoring = true): array
    {
        $pdo = self::pdo();

        $sql = "SELECT id, title, purpose, form_url, COALESCE(link_scope, 'public') AS link_scope, notes
                FROM forms
                WHERE category_id = :category_id
                  AND is_active = 1
                  AND (effective_start IS NULL OR effective_start <= CURDATE())
                  AND (effective_end IS NULL OR effective_end >= CURDATE())";

        if (! $includeMonitoring) {
            $sql .= " AND COALESCE(link_scope, 'public') = 'public' AND purpose = 'pengajuan'";
        }

        $sql .= ' ORDER BY purpose DESC, title';

        $stmt = $pdo->prepare($sql);

        $formsByCategory = [];
        foreach ($categories as $category) {
            $stmt->execute(['category_id' => $category['id']]);
            $rows = self::fetchAll($stmt);

            $normalizedRows = [];
            foreach ($rows as $row) {
                $normalizedUrl = self::normalizeGoogleFormUrl(
                    (string) ($row['form_url'] ?? ''),
                    (string) ($row['link_scope'] ?? 'public'),
                    (string) ($row['purpose'] ?? 'pengajuan')
                );
                if ($normalizedUrl === '') {
                    continue;
                }

                $row['form_url'] = $normalizedUrl;
                $normalizedRows[] = $row;
            }

            $formsByCategory[(int) $category['id']] = $normalizedRows;
        }

        return $formsByCategory;
    }

    public static function portalGetPublicPolicyDocuments(): array
    {
        $pdo = self::pdo();

        return self::fetchAll($pdo->query(
            "SELECT id, code, title, description
             FROM policy_documents
             WHERE is_public = 1
               AND code NOT LIKE 'flow-%'
             ORDER BY id DESC"
        ));
    }

    public static function portalGetFlowDocuments(): array
    {
        $pdo = self::pdo();

        return self::fetchAll($pdo->query(
            "SELECT pd.id, pd.code, pd.title, pd.description,
                    pdr.id AS revision_id, pdr.revision_label, pdr.file_size, pdr.original_filename
             FROM policy_documents pd
             LEFT JOIN policy_document_revisions pdr ON pdr.document_id = pd.id
               AND pdr.id = (
                   SELECT id FROM policy_document_revisions
                   WHERE document_id = pd.id
                   ORDER BY id DESC LIMIT 1
               )
             WHERE pd.is_public = 1
               AND pd.code LIKE 'flow-%'
             ORDER BY pd.id ASC"
        ));
    }

    public static function adminGetMonitoringCategoryStats(): array
    {
        $pdo = self::pdo();

        $rows = self::fetchAll($pdo->query(
            "SELECT c.id,
                    c.code,
                    c.name,
                    SUM(CASE WHEN f.purpose = 'pengajuan' AND f.is_active = 1 THEN 1 ELSE 0 END) AS active_pengajuan_count,
                    SUM(CASE WHEN f.purpose = 'monitoring' AND f.is_active = 1 THEN 1 ELSE 0 END) AS active_monitoring_count,
                    (
                        SELECT f2.id
                        FROM forms f2
                        WHERE f2.category_id = c.id
                          AND f2.purpose = 'monitoring'
                          AND f2.is_active = 1
                        ORDER BY f2.id
                        LIMIT 1
                    ) AS active_monitoring_form_id,
                    (
                        SELECT f3.form_url
                        FROM forms f3
                        WHERE f3.category_id = c.id
                          AND f3.purpose = 'monitoring'
                          AND f3.is_active = 1
                        ORDER BY f3.id
                        LIMIT 1
                    ) AS active_monitoring_form_url,
                    (
                        SELECT COALESCE(f4.link_scope, 'public')
                        FROM forms f4
                        WHERE f4.category_id = c.id
                          AND f4.purpose = 'monitoring'
                          AND f4.is_active = 1
                        ORDER BY f4.id
                        LIMIT 1
                    ) AS active_monitoring_link_scope
             FROM categories c
             LEFT JOIN forms f ON f.category_id = c.id
             WHERE c.is_active = 1
             GROUP BY c.id, c.code, c.name
             ORDER BY c.sort_order, c.name"
        ));

        foreach ($rows as &$row) {
            $row['id'] = (int) ($row['id'] ?? 0);
            $row['code'] = (string) ($row['code'] ?? '');
            $row['name'] = (string) ($row['name'] ?? '');
            $row['active_pengajuan_count'] = (int) ($row['active_pengajuan_count'] ?? 0);
            $row['active_monitoring_count'] = (int) ($row['active_monitoring_count'] ?? 0);
            $row['active_monitoring_form_id'] = (int) ($row['active_monitoring_form_id'] ?? 0);
            $row['active_monitoring_form_url'] = (string) ($row['active_monitoring_form_url'] ?? '');
            $row['active_monitoring_link_scope'] = (string) ($row['active_monitoring_link_scope'] ?? 'public');
        }
        unset($row);

        return $rows;
    }

    public static function adminFindActiveMonitoringFormById(int $formId): ?array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            "SELECT f.id,
                    f.category_id,
                    f.title,
                    f.form_url,
                    COALESCE(f.link_scope, 'public') AS link_scope,
                    c.code AS category_code,
                    c.name AS category_name
             FROM forms f
             JOIN categories c ON c.id = f.category_id
             WHERE f.id = :id
               AND f.purpose = 'monitoring'
               AND f.is_active = 1
               AND c.is_active = 1
             LIMIT 1"
        );

        $stmt->execute(['id' => $formId]);

        return self::fetchOne($stmt);
    }

    public static function portalGetInternalGroups(): array
    {
        $pdo = self::pdo();

        return self::fetchAll($pdo->query(
            'SELECT g.group_name, c.company_name, c.is_manual_input_allowed
             FROM internal_company_groups g
             JOIN internal_companies c ON c.group_id = g.id
             ORDER BY g.sort_order, c.sort_order, c.company_name'
        ));
    }

    public static function portalGetRequiredDocs(): array
    {
        $pdo = self::pdo();

        return self::fetchAll($pdo->query(
            'SELECT doc_scope, doc_name, is_conditional, condition_notes
             FROM required_documents
             WHERE is_active = 1
             ORDER BY doc_scope, sort_order'
        ));
    }

    public static function portalGetSapkonBuckets(): array
    {
        $pdo = self::pdo();

        $rows = self::fetchAll($pdo->query(
            'SELECT s.sapkon_code, s.sapkon_name, b.form_type, b.form_url
             FROM sapkon_companies s
             JOIN sapkon_form_buckets b ON b.sapkon_id = s.id
             WHERE b.is_active = 1
             ORDER BY s.sapkon_code, b.form_type'
        ));

        $normalizedRows = [];
        foreach ($rows as $row) {
            $normalizedUrl = self::normalizeGoogleFormUrl((string) ($row['form_url'] ?? ''));
            if ($normalizedUrl === '') {
                continue;
            }

            $row['form_url'] = $normalizedUrl;
            $normalizedRows[] = $row;
        }

        return $normalizedRows;
    }

    public static function portalCountTotalForms(array $formsByCategory): int
    {
        return (int) array_sum(array_map('count', $formsByCategory));
    }

    public static function countFormsExpiringSoon(int $days = 7): int
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM forms
             WHERE is_active = 1
               AND effective_end IS NOT NULL
               AND effective_end >= CURDATE()
               AND effective_end <= DATE_ADD(CURDATE(), INTERVAL :days DAY)'
        );

        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function getLastFormSyncAt(): string
    {
        $pdo = self::pdo();

        $value = (string) ($pdo->query(
            "SELECT DATE_FORMAT(MAX(updated_at), '%d %b %Y %H:%i') FROM forms"
        )->fetchColumn() ?: '-');

        return $value;
    }

    public static function adminGetDashboardStats(): array
    {
        $pdo = self::pdo();

        $stats = [
            'categories' => (int) $pdo->query('SELECT COUNT(*) FROM categories WHERE is_active = 1')->fetchColumn(),
            'forms' => (int) $pdo->query('SELECT COUNT(*) FROM forms WHERE is_active = 1')->fetchColumn(),
            'sapkon' => (int) $pdo->query('SELECT COUNT(*) FROM sapkon_companies')->fetchColumn(),
            'live_forms' => 0,
            'expiring_soon' => 0,
            'conflicts_prevented_30d' => 0,
            'last_form_update_at' => null,
        ];

        $stats['live_forms'] = (int) $pdo->query(
            'SELECT COUNT(*)
             FROM forms
             WHERE is_active = 1
               AND (effective_start IS NULL OR effective_start <= CURDATE())
               AND (effective_end IS NULL OR effective_end >= CURDATE())'
        )->fetchColumn();

        $stats['expiring_soon'] = self::countFormsExpiringSoon(7);

        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM audit_logs
                 WHERE action IN ('form.create_conflict_prevented', 'form.update_conflict_prevented')
                   AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $stmt->execute();
            $stats['conflicts_prevented_30d'] = (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            $stats['conflicts_prevented_30d'] = 0;
        }

        try {
            $stats['last_form_update_at'] = $pdo->query('SELECT MAX(updated_at) FROM forms')->fetchColumn() ?: null;
        } catch (Throwable $e) {
            $stats['last_form_update_at'] = null;
        }

        return $stats;
    }

    public static function adminGetActiveCategories(): array
    {
        $pdo = self::pdo();

        return self::fetchAll($pdo->query(
            'SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name'
        ));
    }

    public static function adminCategoryIsActive(int $categoryId): bool
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id AND is_active = 1 LIMIT 1');
        $stmt->execute(['id' => $categoryId]);

        return (bool) $stmt->fetchColumn();
    }

    public static function adminGetFormsForManagement(): array
    {
        $pdo = self::pdo();

        return self::fetchAll($pdo->query(
            "SELECT f.id,
                    f.category_id,
                    f.title,
                    f.purpose,
                    f.form_url,
                    f.gdrive_folder_id,
                    COALESCE(f.link_scope, 'public') AS link_scope,
                    f.notes,
                    f.effective_start,
                    f.effective_end,
                    f.is_active,
                    f.updated_at,
                    c.name AS category_name
             FROM forms f
             JOIN categories c ON c.id = f.category_id
             ORDER BY c.sort_order, c.name, f.purpose DESC, f.title"
        ));
    }

    public static function adminFindFormById(int $formId): ?array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            "SELECT id,
                    category_id,
                    title,
                    purpose,
                    form_url,
                    COALESCE(link_scope, 'public') AS link_scope,
                    notes,
                    effective_start,
                    effective_end,
                    is_active,
                    created_by,
                    updated_by
             FROM forms
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute(['id' => $formId]);

        return self::fetchOne($stmt);
    }

    public static function adminCreateForm(array $data, int $adminUserId): int
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            'INSERT INTO forms (category_id, title, purpose, form_url, gdrive_folder_id, link_scope, notes, effective_start, effective_end, is_active, created_by, updated_by)
             VALUES (:category_id, :title, :purpose, :form_url, :gdrive_folder_id, :link_scope, :notes, :effective_start, :effective_end, 1, :created_by, :updated_by)'
        );

        $stmt->execute([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'purpose' => $data['purpose'],
            'form_url' => $data['form_url'],
            'gdrive_folder_id' => $data['gdrive_folder_id'] ?? null,
            'link_scope' => $data['link_scope'],
            'notes' => $data['notes'] !== '' ? $data['notes'] : null,
            'effective_start' => $data['effective_start'],
            'effective_end' => $data['effective_end'],
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function adminUpdateForm(int $formId, array $data, int $adminUserId): void
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            'UPDATE forms
             SET title = :title,
                 purpose = :purpose,
                 form_url = :form_url,
                 gdrive_folder_id = :gdrive_folder_id,
                 link_scope = :link_scope,
                 notes = :notes,
                 effective_start = :effective_start,
                 effective_end = :effective_end,
                 is_active = :is_active,
                 updated_by = :updated_by
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $formId,
            'title' => $data['title'],
            'purpose' => $data['purpose'],
            'form_url' => $data['form_url'],
            'gdrive_folder_id' => $data['gdrive_folder_id'] ?? null,
            'link_scope' => $data['link_scope'],
            'notes' => $data['notes'] !== '' ? $data['notes'] : null,
            'effective_start' => $data['effective_start'],
            'effective_end' => $data['effective_end'],
            'is_active' => $data['is_active'],
            'updated_by' => $adminUserId,
        ]);
    }

    public static function adminArchiveForm(int $formId, int $adminUserId): void
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare('UPDATE forms SET is_active = 0, updated_by = :updated_by WHERE id = :id');
        $stmt->execute([
            'id' => $formId,
            'updated_by' => $adminUserId,
        ]);
    }

    public static function adminFormExistsByIdentity(int $categoryId, string $title, string $purpose, ?int $excludeFormId = null): bool
    {
        $pdo = self::pdo();

        $sql = 'SELECT id
                FROM forms
                WHERE category_id = :category_id
                  AND title = :title
                  AND purpose = :purpose';

        if ($excludeFormId !== null) {
            $sql .= ' AND id <> :exclude_form_id';
        }

        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);

        $params = [
            'category_id' => $categoryId,
            'title' => $title,
            'purpose' => $purpose,
        ];

        if ($excludeFormId !== null) {
            $params['exclude_form_id'] = $excludeFormId;
        }

        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public static function adminFormExistsByUrl(int $categoryId, string $purpose, string $formUrl, ?int $excludeFormId = null): bool
    {
        $pdo = self::pdo();

        $sql = 'SELECT id
                FROM forms
                WHERE category_id = :category_id
                  AND purpose = :purpose
                  AND form_url = :form_url';

        if ($excludeFormId !== null) {
            $sql .= ' AND id <> :exclude_form_id';
        }

        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);

        $params = [
            'category_id' => $categoryId,
            'purpose' => $purpose,
            'form_url' => $formUrl,
        ];

        if ($excludeFormId !== null) {
            $params['exclude_form_id'] = $excludeFormId;
        }

        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public static function adminGetActiveScheduleConflicts(
        int $categoryId,
        string $purpose,
        ?string $effectiveStart,
        ?string $effectiveEnd,
        ?int $excludeFormId = null,
        int $limit = 25
    ): array {
        $pdo = self::pdo();
        $safeLimit = max(1, min($limit, 100));

        $sql = 'SELECT f.id,
                       f.category_id,
                       f.title,
                       f.purpose,
                       f.form_url,
                       COALESCE(f.link_scope, "public") AS link_scope,
                       f.effective_start,
                       f.effective_end,
                       f.updated_at,
                       c.name AS category_name
                FROM forms f
                JOIN categories c ON c.id = f.category_id
                WHERE f.category_id = :category_id
                  AND f.purpose = :purpose
                  AND f.is_active = 1
                  AND COALESCE(f.effective_start, "1000-01-01") <= COALESCE(:effective_end, "9999-12-31")
                  AND COALESCE(:effective_start, "1000-01-01") <= COALESCE(f.effective_end, "9999-12-31")';

        if ($excludeFormId !== null) {
            $sql .= ' AND f.id <> :exclude_form_id';
        }

        $sql .= ' ORDER BY f.updated_at DESC, f.id DESC LIMIT '.$safeLimit;

        $stmt = $pdo->prepare($sql);
        $params = [
            'category_id' => $categoryId,
            'purpose' => $purpose,
            'effective_start' => $effectiveStart,
            'effective_end' => $effectiveEnd,
        ];

        if ($excludeFormId !== null) {
            $params['exclude_form_id'] = $excludeFormId;
        }

        $stmt->execute($params);

        return self::fetchAll($stmt);
    }

    public static function adminFormHasActiveScheduleConflict(
        int $categoryId,
        string $purpose,
        ?string $effectiveStart,
        ?string $effectiveEnd,
        ?int $excludeFormId = null
    ): bool {
        return count(self::adminGetActiveScheduleConflicts(
            $categoryId,
            $purpose,
            $effectiveStart,
            $effectiveEnd,
            $excludeFormId,
            1
        )) > 0;
    }

    public static function adminWriteAuditLog(
        ?int $actorUserId,
        string $action,
        string $entityType,
        ?int $entityId,
        ?array $beforeState,
        ?array $afterState,
        string $ipAddress,
        string $userAgent
    ): void {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, before_state, after_state, ip_address, user_agent)
             VALUES (:actor_user_id, :action, :entity_type, :entity_id, :before_state, :after_state, :ip_address, :user_agent)'
        );

        $stmt->execute([
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_state' => $beforeState !== null ? json_encode($beforeState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'after_state' => $afterState !== null ? json_encode($afterState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'ip_address' => mb_substr($ipAddress, 0, 45),
            'user_agent' => mb_substr($userAgent, 0, 255),
        ]);
    }

    public static function adminGetAuditFilterOptions(): array
    {
        $pdo = self::pdo();

        $actions = $pdo->query('SELECT DISTINCT action FROM audit_logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $entityTypes = $pdo->query('SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type')->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return [
            'actions' => array_values(array_filter(array_map('strval', $actions))),
            'entity_types' => array_values(array_filter(array_map('strval', $entityTypes))),
        ];
    }

    public static function adminGetAuditLogs(array $filters = [], int $limit = 150): array
    {
        $pdo = self::pdo();

        $safeLimit = max(20, min($limit, 300));

        $sql = 'SELECT a.id,
                       a.action,
                       a.entity_type,
                       a.entity_id,
                       a.before_state,
                       a.after_state,
                       a.ip_address,
                       a.user_agent,
                       a.created_at,
                       u.email AS actor_email,
                       u.full_name AS actor_name
                FROM audit_logs a
                LEFT JOIN users u ON u.id = a.actor_user_id
                WHERE 1=1';

        $params = [];

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $sql .= ' AND a.action = :action';
            $params['action'] = $action;
        }

        $entityType = trim((string) ($filters['entity_type'] ?? ''));
        if ($entityType !== '') {
            $sql .= ' AND a.entity_type = :entity_type';
            $params['entity_type'] = $entityType;
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (
                a.action LIKE :q
                OR a.entity_type LIKE :q
                OR CAST(a.entity_id AS CHAR) LIKE :q
                OR COALESCE(u.email, "") LIKE :q
                OR COALESCE(u.full_name, "") LIKE :q
                OR COALESCE(a.ip_address, "") LIKE :q
            )';
            $params['q'] = '%'.$q.'%';
        }

        $sql .= ' ORDER BY a.id DESC LIMIT '.$safeLimit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::fetchAll($stmt);
    }

    public static function adminGetDashboardDailyTrend(int $days = 7): array
    {
        $pdo = self::pdo();

        $safeDays = max(3, min($days, 30));
        $today = new DateTimeImmutable('today');

        $liveStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM forms
             WHERE is_active = 1
               AND (effective_start IS NULL OR effective_start <= :ref_date_1)
               AND (effective_end IS NULL OR effective_end >= :ref_date_2)'
        );

        $conflictStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM audit_logs
             WHERE action IN ('form.create_conflict_prevented', 'form.update_conflict_prevented')
               AND DATE(created_at) = :ref_date"
        );

        $rows = [];

        for ($i = $safeDays - 1; $i >= 0; $i--) {
            $date = $today->sub(new DateInterval('P'.$i.'D'))->format('Y-m-d');

            $liveStmt->execute(['ref_date_1' => $date, 'ref_date_2' => $date]);
            $liveForms = (int) $liveStmt->fetchColumn();

            $conflictStmt->execute(['ref_date' => $date]);
            $conflicts = (int) $conflictStmt->fetchColumn();

            $rows[] = [
                'date' => $date,
                'label' => (new DateTimeImmutable($date))->format('d M'),
                'live_forms' => $liveForms,
                'conflicts_prevented' => $conflicts,
            ];
        }

        return $rows;
    }

    public static function adminGetDashboardWeeklyTrend(int $weeks = 8): array
    {
        $pdo = self::pdo();

        $safeWeeks = max(4, min($weeks, 16));
        $thisWeekStart = (new DateTimeImmutable('today'))->modify('monday this week');

        $liveStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM forms
             WHERE is_active = 1
               AND (effective_start IS NULL OR effective_start <= :ref_date_1)
               AND (effective_end IS NULL OR effective_end >= :ref_date_2)'
        );

        $conflictStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM audit_logs
             WHERE action IN ('form.create_conflict_prevented', 'form.update_conflict_prevented')
               AND created_at >= :start_at
               AND created_at < :end_at"
        );

        $rows = [];

        for ($i = $safeWeeks - 1; $i >= 0; $i--) {
            $weekStart = $thisWeekStart->sub(new DateInterval('P'.($i * 7).'D'));
            $weekEnd = $weekStart->add(new DateInterval('P6D'));
            $weekEndNext = $weekEnd->add(new DateInterval('P1D'));

            $liveRefDate = $weekEnd->format('Y-m-d');
            $liveStmt->execute(['ref_date_1' => $liveRefDate, 'ref_date_2' => $liveRefDate]);
            $liveForms = (int) $liveStmt->fetchColumn();

            $conflictStmt->execute([
                'start_at' => $weekStart->format('Y-m-d 00:00:00'),
                'end_at' => $weekEndNext->format('Y-m-d 00:00:00'),
            ]);
            $conflicts = (int) $conflictStmt->fetchColumn();

            $rows[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'label' => $weekStart->format('d M').' - '.$weekEnd->format('d M'),
                'live_forms' => $liveForms,
                'conflicts_prevented' => $conflicts,
            ];
        }

        return $rows;
    }

    public static function adminGetExpiringForms(int $days = 7, int $limit = 8): array
    {
        $pdo = self::pdo();

        $safeDays = max(1, min($days, 30));
        $safeLimit = max(1, min($limit, 30));

        $sql = 'SELECT f.id,
                       f.title,
                       f.purpose,
                       f.effective_end,
                       DATEDIFF(f.effective_end, CURDATE()) AS days_left,
                       c.name AS category_name
                FROM forms f
                JOIN categories c ON c.id = f.category_id
                WHERE f.is_active = 1
                  AND (f.effective_start IS NULL OR f.effective_start <= CURDATE())
                  AND f.effective_end IS NOT NULL
                  AND f.effective_end >= CURDATE()
                  AND f.effective_end <= DATE_ADD(CURDATE(), INTERVAL '.$safeDays.' DAY)
                ORDER BY f.effective_end ASC, c.sort_order ASC, c.name ASC, f.title ASC
                LIMIT '.$safeLimit;

        return self::fetchAll($pdo->query($sql));
    }

    public static function adminGetConflictHeatmapByCategory(int $days = 30): array
    {
        $pdo = self::pdo();

        $safeDays = max(7, min($days, 120));

        $categories = self::fetchAll($pdo->query(
            'SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name'
        ));

        $counts = [];
        foreach ($categories as $category) {
            $counts[(int) $category['id']] = [
                'category_id' => (int) $category['id'],
                'category_name' => (string) $category['name'],
                'conflicts_prevented' => 0,
            ];
        }

        $stmt = $pdo->prepare(
            "SELECT before_state, after_state
             FROM audit_logs
             WHERE action IN ('form.create_conflict_prevented', 'form.update_conflict_prevented')
               AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             ORDER BY id DESC
             LIMIT 5000"
        );

        $stmt->bindValue(':days', $safeDays, PDO::PARAM_INT);
        $stmt->execute();
        $rows = self::fetchAll($stmt);

        foreach ($rows as $row) {
            $beforeState = json_decode((string) ($row['before_state'] ?? ''), true);
            $afterState = json_decode((string) ($row['after_state'] ?? ''), true);

            $categoryId = 0;

            if (is_array($afterState)) {
                $attemptedData = $afterState['attempted_data'] ?? null;
                if (is_array($attemptedData) && isset($attemptedData['category_id'])) {
                    $categoryId = (int) $attemptedData['category_id'];
                }

                if ($categoryId < 1 && isset($afterState['category_id'])) {
                    $categoryId = (int) $afterState['category_id'];
                }
            }

            if ($categoryId < 1 && is_array($beforeState) && isset($beforeState['category_id'])) {
                $categoryId = (int) $beforeState['category_id'];
            }

            if ($categoryId > 0 && isset($counts[$categoryId])) {
                $counts[$categoryId]['conflicts_prevented']++;
            }
        }

        return array_values($counts);
    }

    public static function adminGetPolicyDocumentByCode(string $code): ?array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            'SELECT d.id,
                    d.code,
                    d.title,
                    d.description,
                    d.is_public,
                    d.created_at,
                    d.updated_at,
                    r.id AS revision_id,
                    r.revision_label,
                    r.original_filename,
                    r.stored_path,
                    r.mime_type,
                    r.file_size,
                    r.checksum_sha256,
                    r.notes AS revision_notes,
                    r.created_at AS revision_created_at
             FROM policy_documents d
             LEFT JOIN policy_document_revisions r ON r.id = (
                SELECT r2.id
                FROM policy_document_revisions r2
                WHERE r2.document_id = d.id
                ORDER BY r2.id DESC
                LIMIT 1
             )
             WHERE d.code = :code
             LIMIT 1'
        );

        $stmt->execute(['code' => $code]);

        return self::fetchOne($stmt);
    }

    public static function adminGetPolicyDocumentRevisions(int $documentId, int $limit = 30): array
    {
        $pdo = self::pdo();

        $safeLimit = max(1, min($limit, 100));

        $stmt = $pdo->prepare(
            'SELECT id,
                    revision_label,
                    original_filename,
                    stored_path,
                    mime_type,
                    file_size,
                    checksum_sha256,
                    notes,
                    uploaded_by,
                    created_at
             FROM policy_document_revisions
             WHERE document_id = :document_id
             ORDER BY id DESC
             LIMIT '.$safeLimit
        );

        $stmt->execute(['document_id' => $documentId]);

        return self::fetchAll($stmt);
    }

    public static function adminUpsertPolicyDocumentMeta(
        string $code,
        string $title,
        ?string $description,
        bool $isPublic,
        ?int $adminUserId
    ): int {
        $pdo = self::pdo();

        $normalizedTitle = trim($title);
        if ($normalizedTitle === '') {
            $normalizedTitle = 'Dokumen Kebijakan';
        }

        $normalizedDescription = $description !== null ? trim($description) : null;
        if ($normalizedDescription === '') {
            $normalizedDescription = null;
        }

        $checkStmt = $pdo->prepare('SELECT id FROM policy_documents WHERE code = :code LIMIT 1');
        $checkStmt->execute(['code' => $code]);
        $existingId = (int) ($checkStmt->fetchColumn() ?: 0);

        if ($existingId > 0) {
            $updateStmt = $pdo->prepare(
                'UPDATE policy_documents
                 SET title = :title,
                     description = :description,
                     is_public = :is_public,
                     updated_by = :updated_by,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );

            $updateStmt->execute([
                'id' => $existingId,
                'title' => $normalizedTitle,
                'description' => $normalizedDescription,
                'is_public' => $isPublic ? 1 : 0,
                'updated_by' => $adminUserId,
            ]);

            return $existingId;
        }

        $insertStmt = $pdo->prepare(
            'INSERT INTO policy_documents (code, title, description, is_public, created_by, updated_by)
             VALUES (:code, :title, :description, :is_public, :created_by, :updated_by)'
        );

        $insertStmt->execute([
            'code' => $code,
            'title' => $normalizedTitle,
            'description' => $normalizedDescription,
            'is_public' => $isPublic ? 1 : 0,
            'created_by' => $adminUserId,
            'updated_by' => $adminUserId,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function adminAddPolicyDocumentRevision(int $documentId, array $data, ?int $adminUserId): int
    {
        $pdo = self::pdo();

        $checksum = strtolower(trim((string) ($data['checksum_sha256'] ?? '')));
        if (! preg_match('/^[a-f0-9]{64}$/', $checksum)) {
            $checksum = str_repeat('0', 64);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO policy_document_revisions
                (document_id, revision_label, original_filename, stored_path, mime_type, file_size, checksum_sha256, notes, uploaded_by)
             VALUES
                (:document_id, :revision_label, :original_filename, :stored_path, :mime_type, :file_size, :checksum_sha256, :notes, :uploaded_by)'
        );

        $stmt->execute([
            'document_id' => $documentId,
            'revision_label' => trim((string) ($data['revision_label'] ?? '')),
            'original_filename' => trim((string) ($data['original_filename'] ?? 'document.pdf')),
            'stored_path' => trim((string) ($data['stored_path'] ?? '')),
            'mime_type' => trim((string) ($data['mime_type'] ?? 'application/pdf')),
            'file_size' => max(0, (int) ($data['file_size'] ?? 0)),
            'checksum_sha256' => $checksum,
            'notes' => isset($data['notes']) && trim((string) $data['notes']) !== '' ? trim((string) $data['notes']) : null,
            'uploaded_by' => $adminUserId,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function portalGetPublicPolicyDocumentByCode(string $code): ?array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            'SELECT d.id,
                    d.code,
                    d.title,
                    d.description,
                    d.updated_at,
                    r.id AS revision_id,
                    r.revision_label,
                    r.original_filename,
                    r.stored_path,
                    r.mime_type,
                    r.file_size,
                    r.notes AS revision_notes,
                    r.created_at AS revision_created_at
             FROM policy_documents d
             LEFT JOIN policy_document_revisions r ON r.id = (
                SELECT r2.id
                FROM policy_document_revisions r2
                WHERE r2.document_id = d.id
                ORDER BY r2.id DESC
                LIMIT 1
             )
             WHERE d.code = :code
               AND d.is_public = 1
             LIMIT 1'
        );

        $stmt->execute(['code' => $code]);

        return self::fetchOne($stmt);
    }

    public static function portalGetPublicPolicyDocumentRevisions(int $documentId, int $limit = 10): array
    {
        $pdo = self::pdo();

        $safeLimit = max(1, min($limit, 30));

        $stmt = $pdo->prepare(
            'SELECT id,
                    revision_label,
                    original_filename,
                    file_size,
                    notes,
                    created_at
             FROM policy_document_revisions
             WHERE document_id = :document_id
             ORDER BY id DESC
             LIMIT '.$safeLimit
        );

        $stmt->execute(['document_id' => $documentId]);

        return self::fetchAll($stmt);
    }

    public static function portalGetPublicPolicyDocumentRevisionById(int $documentId, int $revisionId): ?array
    {
        $pdo = self::pdo();

        $stmt = $pdo->prepare(
            'SELECT id,
                    document_id,
                    revision_label,
                    original_filename,
                    stored_path,
                    mime_type,
                    file_size,
                    notes,
                    created_at
             FROM policy_document_revisions
             WHERE document_id = :document_id
               AND id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'document_id' => $documentId,
            'id' => $revisionId,
        ]);

        return self::fetchOne($stmt);
    }
}
