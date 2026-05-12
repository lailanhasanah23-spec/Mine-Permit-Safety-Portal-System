<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SimperSubmissionMail;
use App\Models\GoogleToken;
use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class EmailSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $admin = LegacyAuth::user();
        $filters = [
            'status' => trim((string) $request->query('status', '')),
            'submission_type' => trim((string) $request->query('submission_type', '')),
            'q' => trim((string) $request->query('q', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $query = DB::table('email_submissions as es')
            ->select(
                'es.id',
                'es.submission_type',
                'es.applicant_name',
                'es.company_name',
                'es.reference_no',
                'es.recipient_to',
                'es.template_id',
                'es.email_subject',
                'es.status',
                'es.sent_at',
                'es.last_error',
                'es.created_at',
                'u.email as creator_email',
                't.template_name'
            )
            ->leftJoin('users as u', 'u.id', '=', 'es.created_by')
            ->leftJoin('email_submission_templates as t', 't.id', '=', 'es.template_id');

        if (in_array($filters['status'], ['draft', 'sent', 'failed'], true)) {
            $query->where('es.status', $filters['status']);
        }

        if (in_array($filters['submission_type'], ['perpanjangan', 'new_hire'], true)) {
            $query->where('es.submission_type', $filters['submission_type']);
        }

        if ($filters['q'] !== '') {
            $keyword = '%'.$filters['q'].'%';
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('es.applicant_name', 'like', $keyword)
                    ->orWhere('es.company_name', 'like', $keyword)
                    ->orWhere('es.reference_no', 'like', $keyword)
                    ->orWhere('es.email_subject', 'like', $keyword)
                    ->orWhere('es.recipient_to', 'like', $keyword);
            });
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('es.created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('es.created_at', '<=', $filters['date_to']);
        }

        $submissions = $query
            ->orderByDesc('es.id')
            ->limit(250)
            ->get()
            ->map(static fn ($row): array => (array) $row)
            ->all();

        $submissionIds = array_values(array_filter(array_map(
            static fn (array $row): int => (int) ($row['id'] ?? 0),
            $submissions
        ), static fn (int $id): bool => $id > 0));

        $attachmentCountBySubmission = [];
        if ($submissionIds !== []) {
            $attachmentCountBySubmission = DB::table('email_submission_attachments')
                ->select('submission_id', DB::raw('COUNT(*) as total_files'))
                ->whereIn('submission_id', $submissionIds)
                ->groupBy('submission_id')
                ->get()
                ->mapWithKeys(static fn ($row): array => [(int) $row->submission_id => (int) $row->total_files])
                ->all();
        }

        $templates = DB::table('email_submission_templates')
            ->select('id', 'template_name', 'template_code', 'submission_type', 'recipient_cc', 'recipient_bcc', 'subject_template', 'body_template', 'is_active', 'updated_at')
            ->orderByDesc('is_active')
            ->orderBy('template_name')
            ->get()
            ->map(static fn ($row): array => (array) $row)
            ->all();

        $summary = [
            'total' => (int) DB::table('email_submissions')->count(),
            'draft' => (int) DB::table('email_submissions')->where('status', 'draft')->count(),
            'sent' => (int) DB::table('email_submissions')->where('status', 'sent')->count(),
            'failed' => (int) DB::table('email_submissions')->where('status', 'failed')->count(),
        ];

        $userRole = DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin';

        return view('admin.email-submissions', [
            'admin' => $admin,
            'userRole' => $userRole,
            'submissions' => $submissions,
            'templates' => $templates,
            'attachmentCountBySubmission' => $attachmentCountBySubmission,
            'summary' => $summary,
            'filters' => $filters,
            'success' => session('success'),
            'error' => session('error'),
            'defaultMailer' => (string) config('admin_email_workflow.mailer', 'smtp'),
            'allowedEmails' => (array) config('admin_email_workflow.allowed_emails', []),
            'allowedUserIds' => (array) config('admin_email_workflow.allowed_user_ids', []),
            'isGoogleLinked' => GoogleToken::where('service_name', 'gmail')->exists(),
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $validated = $request->validate([
            'template_id' => ['nullable', 'integer', 'min:1'],
            'template_name' => ['required', 'string', 'max:190'],
            'submission_type' => ['required', Rule::in(['perpanjangan', 'new_hire', 'general'])],
            'recipient_cc' => ['nullable', 'string', 'max:500'],
            'recipient_bcc' => ['nullable', 'string', 'max:500'],
            'subject_template' => ['required', 'string', 'max:190'],
            'body_template' => ['required', 'string', 'max:20000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $templateId = (int) ($validated['template_id'] ?? 0);
        $templateCode = Str::slug((string) $validated['template_name']);
        if ($templateCode === '') {
            $templateCode = 'template-email-simper';
        }

        $now = now();
        if ($templateId > 0) {
            $exists = DB::table('email_submission_templates')->where('id', $templateId)->exists();
            if (! $exists) {
                return redirect()->route('admin.email-submissions.php')->with('error', 'Template email tidak ditemukan.');
            }

            DB::table('email_submission_templates')
                ->where('id', $templateId)
                ->update([
                    'template_name' => (string) $validated['template_name'],
                    'submission_type' => (string) $validated['submission_type'],
                    'recipient_cc' => trim((string) ($validated['recipient_cc'] ?? '')) ?: null,
                    'recipient_bcc' => trim((string) ($validated['recipient_bcc'] ?? '')) ?: null,
                    'subject_template' => (string) $validated['subject_template'],
                    'body_template' => (string) $validated['body_template'],
                    'is_active' => $request->boolean('is_active'),
                    'updated_by' => (int) $admin['id'],
                    'updated_at' => $now,
                ]);

            $savedTemplateId = $templateId;
            $auditAction = 'email_template.update';
        } else {
            $savedTemplateId = (int) DB::table('email_submission_templates')->insertGetId([
                'template_code' => $this->resolveUniqueTemplateCode($templateCode),
                'template_name' => (string) $validated['template_name'],
                'submission_type' => (string) $validated['submission_type'],
                'recipient_cc' => trim((string) ($validated['recipient_cc'] ?? '')) ?: null,
                'recipient_bcc' => trim((string) ($validated['recipient_bcc'] ?? '')) ?: null,
                'subject_template' => (string) $validated['subject_template'],
                'body_template' => (string) $validated['body_template'],
                'is_active' => $request->boolean('is_active', true),
                'created_by' => (int) $admin['id'],
                'updated_by' => (int) $admin['id'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $auditAction = 'email_template.create';
        }

        $this->writeAuditSafe(
            (int) $admin['id'],
            $auditAction,
            'email_submission_templates',
            $savedTemplateId,
            null,
            ['template_name' => (string) $validated['template_name'], 'submission_type' => (string) $validated['submission_type']],
            (string) $request->ip(),
            (string) $request->userAgent()
        );

        return redirect()->route('admin.email-submissions.php')->with('success', 'Template email berhasil disimpan.');
    }

    public function storeSubmission(Request $request)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $validated = $request->validate([
            'submission_type' => ['required', Rule::in(['perpanjangan', 'new_hire'])],
            'applicant_name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'reference_no' => ['nullable', 'string', 'max:80'],
            'recipient_to' => ['required', 'string', 'max:500'],
            'recipient_cc' => ['nullable', 'string', 'max:500'],
            'recipient_bcc' => ['nullable', 'string', 'max:500'],
            'template_id' => ['nullable', 'integer', 'min:1'],
            'email_subject' => ['nullable', 'string', 'max:190'],
            'email_body' => ['nullable', 'string', 'max:20000'],
            'doc_sim' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_ktp' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_fu' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_mcu' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_other.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'doc_other' => ['nullable', 'array', 'max:10'],
            'action_intent' => ['nullable', Rule::in(['save_draft', 'send_now'])],
        ]);

        $actionIntent = (string) ($validated['action_intent'] ?? 'save_draft');

        $recipientValidation = $this->parseEmailListWithInvalids((string) ($validated['recipient_to'] ?? ''));
        if ($recipientValidation['valid'] === []) {
            return redirect()->route('admin.email-submissions.php')
                ->with('error', 'Format email penerima utama (To) tidak valid.');
        }

        $requiredDocFields = [
            'doc_sim' => 'SIM',
            'doc_ktp' => 'KTP',
            'doc_fu' => 'FU',
            'doc_mcu' => 'MCU',
        ];

        if ($actionIntent === 'send_now') {
            $missingLabels = [];
            foreach ($requiredDocFields as $field => $label) {
                $file = $request->file($field);
                if (! $file || ! $file->isValid()) {
                    $missingLabels[] = $label;
                }
            }

            if ($missingLabels !== []) {
                return redirect()->route('admin.email-submissions.php')
                    ->with('error', 'Kirim email membutuhkan lampiran wajib: '.implode(', ', $missingLabels).'.');
            }
        }

        $template = null;
        $templateId = (int) ($validated['template_id'] ?? 0);
        if ($templateId > 0) {
            $template = DB::table('email_submission_templates')
                ->select('id', 'recipient_cc', 'recipient_bcc', 'subject_template', 'body_template')
                ->where('id', $templateId)
                ->where('is_active', 1)
                ->first();
        }

        $placeholders = [
            '{{submission_type}}' => (string) $validated['submission_type'],
            '{{submission_type_label}}' => $this->submissionTypeLabel((string) $validated['submission_type']),
            '{{applicant_name}}' => trim((string) ($validated['applicant_name'] ?? '-')),
            '{{company_name}}' => trim((string) ($validated['company_name'] ?? '-')),
            '{{reference_no}}' => trim((string) ($validated['reference_no'] ?? '-')),
            '{{request_date}}' => date('d M Y H:i'),
        ];

        $resolvedSubject = trim((string) ($validated['email_subject'] ?? ''));
        $resolvedBody = trim((string) ($validated['email_body'] ?? ''));
        $resolvedCc = trim((string) ($validated['recipient_cc'] ?? ''));
        $resolvedBcc = trim((string) ($validated['recipient_bcc'] ?? ''));

        if ($template !== null) {
            if ($resolvedCc === '') {
                $resolvedCc = trim((string) ($template->recipient_cc ?? ''));
            }
            if ($resolvedBcc === '') {
                $resolvedBcc = trim((string) ($template->recipient_bcc ?? ''));
            }
            if ($resolvedSubject === '') {
                $resolvedSubject = $this->applyTemplatePlaceholders((string) ($template->subject_template ?? ''), $placeholders);
            }
            if ($resolvedBody === '') {
                $resolvedBody = $this->applyTemplatePlaceholders((string) ($template->body_template ?? ''), $placeholders);
            }
        }

        if ($resolvedSubject === '' || $resolvedBody === '') {
            return redirect()->route('admin.email-submissions.php')
                ->with('error', 'Subject dan body email wajib diisi, atau pilih template aktif.');
        }

        $now = now();

        $submissionId = (int) DB::table('email_submissions')->insertGetId([
            'submission_type' => (string) $validated['submission_type'],
            'applicant_name' => (string) $validated['applicant_name'],
            'company_name' => trim((string) ($validated['company_name'] ?? '')) ?: null,
            'reference_no' => trim((string) ($validated['reference_no'] ?? '')) ?: null,
            'recipient_to' => (string) $validated['recipient_to'],
            'recipient_cc' => $resolvedCc !== '' ? $resolvedCc : null,
            'recipient_bcc' => $resolvedBcc !== '' ? $resolvedBcc : null,
            'template_id' => $templateId > 0 ? $templateId : null,
            'email_subject' => $resolvedSubject,
            'email_body' => $resolvedBody,
            'delivery_channel' => 'gmail',
            'status' => 'draft',
            'created_by' => (int) $admin['id'],
            'updated_by' => (int) $admin['id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->storeSubmissionAttachments($request, $submissionId);

        $this->writeAuditSafe(
            (int) $admin['id'],
            'email_submission.create',
            'email_submissions',
            $submissionId,
            null,
            ['submission_type' => (string) $validated['submission_type'], 'applicant_name' => (string) $validated['applicant_name']],
            (string) $request->ip(),
            (string) $request->userAgent()
        );

        if ($actionIntent === 'send_now') {
            return $this->send($request, $submissionId);
        }

        return redirect()->route('admin.email-submissions.php')
            ->with('success', 'Draft pengajuan email berhasil disimpan.');
    }

    public function send(Request $request, int $id)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $submission = DB::table('email_submissions')->where('id', $id)->first();
        if (! $submission) {
            return redirect()->route('admin.email-submissions.php')->with('error', 'Data pengajuan email tidak ditemukan.');
        }

        $toParse = $this->parseEmailListWithInvalids((string) ($submission->recipient_to ?? ''));
        $ccParse = $this->parseEmailListWithInvalids((string) ($submission->recipient_cc ?? ''));
        $bccParse = $this->parseEmailListWithInvalids((string) ($submission->recipient_bcc ?? ''));

        $toRecipients = $toParse['valid'];
        $ccRecipients = $ccParse['valid'];
        $bccRecipients = $bccParse['valid'];

        if ($toRecipients === []) {
            return redirect()->route('admin.email-submissions.php')->with('error', 'Penerima utama email wajib diisi dengan format yang valid.');
        }

        $invalidTokens = array_values(array_unique(array_merge($toParse['invalid'], $ccParse['invalid'], $bccParse['invalid'])));
        if ($invalidTokens !== []) {
            return redirect()->route('admin.email-submissions.php')
                ->with('error', 'Pengiriman diblokir. Format email tidak valid: '.implode(', ', $invalidTokens).'.');
        }

        $attachmentRows = DB::table('email_submission_attachments')
            ->select('doc_type', 'original_filename', 'stored_path', 'mime_type')
            ->where('submission_id', $id)
            ->orderBy('id')
            ->get();

        $requiredDocTypes = ['sim', 'ktp', 'fu', 'mcu'];
        $availableDocTypes = [];
        foreach ($attachmentRows as $attachmentRow) {
            $docType = trim((string) ($attachmentRow->doc_type ?? ''));
            if ($docType !== '') {
                $availableDocTypes[$docType] = true;
            }
        }

        $missingDocTypes = [];
        foreach ($requiredDocTypes as $requiredDocType) {
            if (! isset($availableDocTypes[$requiredDocType])) {
                $missingDocTypes[] = strtoupper($requiredDocType);
            }
        }

        if ($missingDocTypes !== []) {
            return redirect()->route('admin.email-submissions.php')
                ->with('error', 'Pengiriman diblokir. Lampiran wajib belum lengkap: '.implode(', ', $missingDocTypes).'.');
        }

        $mailAttachments = [];
        foreach ($attachmentRows as $attachmentRow) {
            $relativePath = (string) ($attachmentRow->stored_path ?? '');
            $absolutePath = Storage::disk('local')->path($relativePath);
            if ($relativePath === '' || ! is_file($absolutePath)) {
                continue;
            }

            $mailAttachments[] = [
                'path' => $absolutePath,
                'as' => (string) ($attachmentRow->original_filename ?? basename($absolutePath)),
                'mime' => (string) ($attachmentRow->mime_type ?? 'application/octet-stream'),
            ];
        }

        return DB::transaction(function () use ($id, $admin, $toRecipients, $ccRecipients, $bccRecipients, $submission, $mailAttachments, $request): RedirectResponse {
            // Pessimistic lock to prevent double sending
            $lockedSubmission = DB::table('email_submissions')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if ($lockedSubmission->status === 'sent') {
                return redirect()->route('admin.email-submissions.php')->with('info', 'Email ini sudah terkirim sebelumnya.');
            }

            try {
                $mailer = (string) config('admin_email_workflow.mailer', 'smtp');
                $mail = Mail::mailer($mailer)
                    ->to($toRecipients)
                    ->cc($ccRecipients)
                    ->bcc($bccRecipients);

                $mail->send(new SimperSubmissionMail(
                    (string) ($submission->email_subject ?? '-'),
                    nl2br(e((string) ($submission->email_body ?? ''))),
                    $mailAttachments
                ));

                DB::table('email_submissions')
                    ->where('id', $id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'last_error' => null,
                        'updated_by' => (int) $admin['id'],
                        'updated_at' => now(),
                    ]);

                $this->writeAuditSafe(
                    (int) $admin['id'],
                    'email_submission.sent',
                    'email_submissions',
                    $id,
                    ['status' => (string) ($submission->status ?? 'draft')],
                    ['status' => 'sent'],
                    (string) $request->ip(),
                    (string) $request->userAgent()
                );

                Log::info("Email SIMPER sent successfully. Submission ID: {$id} via Mailer: {$mailer}");

                return redirect()->route('admin.email-submissions.php')->with('success', 'Email pengajuan SIMPER berhasil dikirim via '.$mailer.'.');

            } catch (\Exception $e) {
                DB::table('email_submissions')
                    ->where('id', $id)
                    ->update([
                        'status' => 'failed',
                        'last_error' => substr($e->getMessage(), 0, 1000),
                        'updated_by' => (int) $admin['id'],
                        'updated_at' => now(),
                    ]);

                $this->writeAuditSafe(
                    (int) $admin['id'],
                    'email_submission.failed',
                    'email_submissions',
                    $id,
                    ['status' => (string) ($submission->status ?? 'draft')],
                    ['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 200)],
                    (string) $request->ip(),
                    (string) $request->userAgent()
                );

                Log::error("Email SIMPER failed to send. Submission ID: {$id}. Error: ".$e->getMessage());

                return redirect()->route('admin.email-submissions.php')->with('error', 'Gagal mengirim email: '.$e->getMessage());
            }
        });
    }

    private function resolveUniqueTemplateCode(string $baseCode): string
    {
        $candidate = $baseCode;
        $suffix = 2;

        while (DB::table('email_submission_templates')->where('template_code', $candidate)->exists()) {
            $candidate = $baseCode.'-'.$suffix;
            $suffix++;
            if ($suffix > 99) {
                $candidate = $baseCode.'-'.Str::lower(Str::random(4));
                break;
            }
        }

        return $candidate;
    }

    private function storeSubmissionAttachments(Request $request, int $submissionId): void
    {
        $singleMap = [
            'doc_sim' => 'sim',
            'doc_ktp' => 'ktp',
            'doc_fu' => 'fu',
            'doc_mcu' => 'mcu',
        ];

        foreach ($singleMap as $field => $docType) {
            $file = $request->file($field);
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $this->persistAttachmentFile($submissionId, $file, $docType);
        }

        $otherFiles = $request->file('doc_other', []);
        if (is_array($otherFiles)) {
            foreach ($otherFiles as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }
                $this->persistAttachmentFile($submissionId, $file, 'other');
            }
        }
    }

    private function persistAttachmentFile(int $submissionId, mixed $file, string $docType): void
    {
        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) $file->getClientOriginalName()) ?: 'attachment';
        $storedDir = 'private/email-submissions/'.$submissionId;
        $storedName = date('YmdHis').'-'.$docType.'-'.bin2hex(random_bytes(4)).'-'.trim((string) $safeName, '-');
        $storedPath = $file->storeAs($storedDir, $storedName, ['disk' => 'local']);

        if (! is_string($storedPath) || $storedPath === '') {
            return;
        }

        $absolutePath = Storage::disk('local')->path($storedPath);

        DB::table('email_submission_attachments')->insert([
            'submission_id' => $submissionId,
            'doc_type' => $docType,
            'original_filename' => (string) $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => (string) ($file->getMimeType() ?: 'application/octet-stream'),
            'file_size' => (int) ($file->getSize() ?: 0),
            'checksum_sha256' => (string) (hash_file('sha256', $absolutePath) ?: str_repeat('0', 64)),
            'created_at' => now(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function parseEmailList(string $raw): array
    {
        $parsed = $this->parseEmailListWithInvalids($raw);

        return (array) ($parsed['valid'] ?? []);
    }

    /**
     * @return array{valid: array<int, string>, invalid: array<int, string>}
     */
    private function parseEmailListWithInvalids(string $raw): array
    {
        $parts = preg_split('/[;,\s]+/', trim($raw)) ?: [];
        $validMap = [];
        $invalidMap = [];

        foreach ($parts as $candidate) {
            $email = strtolower(trim((string) $candidate));
            if ($email === '') {
                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalidMap[$email] = $email;

                continue;
            }

            $validMap[$email] = $email;
        }

        return [
            'valid' => array_values($validMap),
            'invalid' => array_values($invalidMap),
        ];
    }

    private function applyTemplatePlaceholders(string $template, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }

    private function submissionTypeLabel(string $type): string
    {
        return $type === 'new_hire' ? 'New Hire' : 'Perpanjangan';
    }

    private function writeAuditSafe(
        int $actorUserId,
        string $action,
        string $entityType,
        ?int $entityId,
        mixed $beforeState,
        mixed $afterState,
        string $ipAddress,
        string $userAgent
    ): void {
        try {
            LegacyRepository::adminWriteAuditLog(
                $actorUserId,
                $action,
                $entityType,
                $entityId,
                is_array($beforeState) ? $beforeState : null,
                is_array($afterState) ? $afterState : null,
                $ipAddress,
                $userAgent
            );
        } catch (Throwable $e) {
            // Keep primary flow alive when audit logging is unavailable.
        }
    }
}
