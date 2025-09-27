<?php
/**
 * Application Configuration
 * Contains secure configuration for URLs and other settings
 */

$app_config = [
    'base_url' => getenv('APP_URL') ?: '',
    'force_https' => getenv('FORCE_HTTPS') === 'true',
    'app_name'    => 'J. Joseph Salon Team Portal',
    'from_email'  => getenv('FROM_EMAIL') ?: 'noreply@jjosephsalon.com'
];

/**
 * Get the secure base URL for the application
 */
function getAppBaseUrl() {
    global $app_config;
    $env = trim($app_config['base_url'] ?? '');

    // If APP_URL is an absolute URL, use it
    if ($env && preg_match('#^https?://#i', $env)) {
        return rtrim($env, '/');
    }

    // Derive from server host if available
    if (!empty($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
        return $scheme . '://' . rtrim($_SERVER['HTTP_HOST'], '/');
    }

    // Fallbacks: local dev or production
    if (file_exists('/Applications/MAMP/tmp/mysql/mysql.sock')) {
        return 'http://portaltest:8888';
    }
    return 'https://portal.jjosephsalon.com';
}

/**
 * Generate a secure signup URL
 */
function getSignupUrl($token) {
    $base = getAppBaseUrl();
    // Ensure /portal path
    if (stripos(parse_url($base, PHP_URL_PATH) ?: '', '/portal') === false) {
        $base = rtrim($base, '/') . '/portal';
    }
    return rtrim($base, '/') . '/signup.php?token=' . urlencode($token);
}

/**
 * Get full portal URL for a relative path under /portal
 * Example: getPortalUrl('dashboard.php')
 */
function getPortalUrl($path = '') {
    $base = getAppBaseUrl();
    if (stripos(parse_url($base, PHP_URL_PATH) ?: '', '/portal') === false) {
        $base = rtrim($base, '/') . '/portal';
    }
    $path = ltrim($path, '/');
    return rtrim($base, '/') . ($path ? '/' . $path : '');
}