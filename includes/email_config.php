<?php

/**
 * Email Configuration for Ryvah Commerce
 * PHPMailer SMTP settings
 */

// Email configuration settings
define('SMTP_HOST', 'smtp.hostinger.com'); // Hostinger SMTP server
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl'); // 'tls' or 'ssl'
define('SMTP_AUTH', true);

// Email credentials - You should set these as environment variables in production
define('SMTP_USERNAME', 'info@ryvahcommerce.com'); // Your email address
define('SMTP_PASSWORD', 'Meldor1!1'); // Your Hostinger email password

// Email settings
define('FROM_EMAIL', 'info@ryvahcommerce.com');
define('FROM_NAME', 'Ryvah Commerce');
define('ADMIN_EMAIL', 'ryvah256@gmail.com');

// Email templates directory
define('EMAIL_TEMPLATES_DIR', __DIR__ . '/email_templates/');