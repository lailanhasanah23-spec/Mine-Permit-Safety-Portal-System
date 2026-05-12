<?php

return [
    'lock_max_attempts' => (int) env('AUTH_LOCK_MAX_ATTEMPTS', 5),
    'lock_seconds' => (int) env('AUTH_LOCK_SECONDS', 900),
    'force_password_change' => env('AUTH_FORCE_PASSWORD_CHANGE', true),
    'min_password_length' => (int) env('AUTH_MIN_PASSWORD_LENGTH', 12),
    // If true, vendors can login using only company name without password
    'vendor_passwordless' => (bool) env('VENDOR_PASSWORDLESS', true),
];
