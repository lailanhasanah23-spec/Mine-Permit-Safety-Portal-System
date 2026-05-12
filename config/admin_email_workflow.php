<?php

$allowedUserIds = array_values(array_filter(array_map(
    static fn (string $value): int => (int) trim($value),
    explode(',', (string) env('ADMIN_EMAIL_ALLOWED_USER_IDS', ''))
), static fn (int $value): bool => $value > 0));

$allowedEmails = array_values(array_filter(array_map(
    static fn (string $value): string => strtolower(trim($value)),
    explode(',', (string) env('ADMIN_EMAIL_ALLOWED_EMAILS', ''))
), static fn (string $value): bool => $value !== ''));

return [
    'enabled' => (bool) env('ADMIN_EMAIL_WORKFLOW_ENABLED', true),

    // Optional strict access: if empty, all authenticated admins can access.
    // Fill one/both lists to restrict specific admins only.
    'allowed_user_ids' => $allowedUserIds,
    'allowed_emails' => $allowedEmails,

    // Should point to a configured mailer. Using 'gmail' for OAuth2 Gmail API transport.
    'mailer' => (string) env('ADMIN_EMAIL_MAILER', 'gmail'),

    'from_address' => (string) env('ADMIN_EMAIL_FROM_ADDRESS', (string) env('MAIL_FROM_ADDRESS', 'noreply@example.com')),
    'from_name' => (string) env('ADMIN_EMAIL_FROM_NAME', (string) env('MAIL_FROM_NAME', 'Safety Portal')),
];
