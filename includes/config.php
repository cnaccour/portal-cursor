<?php
/**
 * Application Configuration
 * Contains secure configuration for URLs and other settings
 */

// Base URL configuration (set this to your domain in production)
$app_config = [
    'base_url' => getenv('APP_URL') ?: 'http://localhost:5000',
    'force_https' => getenv('FORCE_HTTPS') === 'true',
    'app_name' => 'J. Joseph Salon Team Portal',
    'from_email' => getenv('FROM_EMAIL') ?: 'noreply@localhost'
];

/**
 * Get the secure base URL for the application
 */
function getAppBaseUrl() {
    global $app_config;
    
    $base_url = $app_config['base_url'];
    
    // Force HTTPS in production if configured
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