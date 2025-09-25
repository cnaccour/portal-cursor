<?php
// Unified PHP config loader (env-first)
if (getenv('PHP_TZ')) { date_default_timezone_set(getenv('PHP_TZ')); }

function env_get(string $key, $default = null) {
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

return [
    'BASE_URL'     => rtrim(env_get('BASE_URL', ''), '/'),
    'ASSET_PREFIX' => rtrim(env_get('ASSET_PREFIX', ''), '/'),
    'API_BASE'     => rtrim(env_get('API_BASE', '/api'), '/'),
    'FORCE_HTTPS'  => env_get('FORCE_HTTPS', 'false') === 'true',
    'APP_NAME'     => env_get('APP_NAME', 'JJS Team Portal'),

    'DB' => [
        'host' => env_get('DB_HOST', 'localhost'),
        'name' => env_get('DB_NAME', ''),
        'user' => env_get('DB_USER', ''),
        'pass' => env_get('DB_PASS', ''),
    ],

    'SMTP' => [
        'host' => env_get('SMTP_HOST', ''),
        'port' => (int)env_get('SMTP_PORT', 465),
        'secure' => env_get('SMTP_SECURE', 'ssl'),
        'user' => env_get('SMTP_USER', ''),
        'pass' => env_get('SMTP_PASS', ''),
        'from_email' => env_get('FROM_EMAIL', 'noreply@example.com'),
        'from_name'  => env_get('FROM_NAME', 'JJS Team Portal'),
        'to_email'   => env_get('TO_EMAIL', ''),
    ],
];
