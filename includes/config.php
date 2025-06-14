<?php

/**
 * Application configuration
 * 
 * This file contains sensitive configuration data.
 * Make sure to keep this file secure and never commit it to version control.
 */

return [
    // PayPal configuration
    'paypal_client_id' => 'ARbQtWP1vIsYqgrKcL0v2hhlJA6NujGi26UWQz9Z4lsmPosxbSDPfzLSkaHtS8JRSvdysC99W0qvLyCI',
    'paypal_client_secret' => 'EChoMRhi0vy7L_Defl5dqinOMbiWHxRmPG2e3ArjXXRQqHR1vwkg1IvHGTDxzwrOOuQR4n-z8ZteQiGc',
    'paypal_sandbox' => false, // Set to false for production

    // Database configuration
    'db_host' => 'localhost',
    'db_name' => 'ryvahcommerce',
    'db_user' => 'root',
    'db_pass' => '@X6js1488',

    // Security configuration
    'session_lifetime' => 3600, // 1 hour
    'max_login_attempts' => 5,
    'password_min_length' => 8,

    // Rate limiting
    'rate_limit_attempts' => 5,
    'rate_limit_window' => 60, // seconds

    // Logging
    'log_path' => __DIR__ . '/../logs',
    'log_level' => 'error', // debug, info, warning, error

    // Cache
    'redis_host' => '127.0.0.1',
    'redis_port' => 6379,
    'redis_password' => null,

    // Application settings
    'app_name' => 'Ryvah Commerce',
    'app_url' => 'https://ryvahcommerce.com',
    'app_timezone' => 'UTC',
    'app_locale' => 'en_US',

    // Email configuration
    'mail_host' => 'smtp.mailtrap.io',
    'mail_port' => 2525,
    'mail_username' => 'your_username',
    'mail_password' => 'your_password',
    'mail_from_address' => 'noreply@ryvahcommerce.com',
    'mail_from_name' => 'Ryvah Commerce'
];