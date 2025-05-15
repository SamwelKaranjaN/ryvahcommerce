<?php
class SecurityHeaders {
    public static function setHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable HSTS
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.paypal.com https://www.google.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "connect-src 'self' https://api.paypal.com; " .
               "frame-src 'self' https://www.paypal.com;");
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    public static function validateCSRFToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                http_response_code(403);
                die('Invalid CSRF token');
            }
        }
    }

    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Set security headers
SecurityHeaders::setHeaders(); 