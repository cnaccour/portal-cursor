<?php
/**
 * Application Configuration
 * Contains secure configuration for URLs and other settings
 */

$app_config = [
    'base_url' => getenv('APP_URL') 
        ?: (file_exists('/Applications/MAMP/tmp/mysql/mysql.sock')
            ? 'http://portaltest:8888'   // local dev
            : 'https://portal.jjosephsalon.com'), // production
    'force_https' => getenv('FORCE_HTTPS') === 'true',
    'app_name'    => 'J. Joseph Salon Team Portal',
    'from_email'  => getenv('FROM_EMAIL') ?: 'noreply@jjosephsalon.com'
];

/**
 * Get the secure base URL for the application
 */
function getAppBaseUrl() {
    global $app_config;
    $base_url = $app_config['base_url'];

    if ($app_config['force_https'] && strpos($base_url, 'http://') === 0) {
        $base_url = str_replace('http://', 'https://', $base_url);
    }
    return rtrim($base_url, '/');
}

/**
 * Generate a secure signup URL
 */
function getSignupUrl($token) {
    return getAppBaseUrl() . '/signup.php?token=' . urlencode($token);
}