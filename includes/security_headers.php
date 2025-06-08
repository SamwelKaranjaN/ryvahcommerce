<?php
// Security headers configuration
function setSecurityHeaders()
{
    // Skip if page sets its own headers
    if (defined('SKIP_GLOBAL_HEADERS') && SKIP_GLOBAL_HEADERS) {
        return;
    }
    // Content Security Policy
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://www.paypal.com https://www.paypalobjects.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "img-src 'self' data: https: blob: https://www.paypalobjects.com https://stripe.com https://*.paypal.com",
        "connect-src 'self' https://api.stripe.com https://api.paypal.com https://www.paypal.com wss://*.stripe.com https://*.paypal.com",
        "frame-src 'self' https://js.stripe.com https://www.paypal.com https://*.paypal.com",
        "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self' https://www.paypal.com",
        "worker-src 'self' blob:",
        "child-src 'self' blob:"
    ];

    header("Content-Security-Policy: " . implode("; ", $csp));

    // Other security headers
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

    // Remove server information
    header_remove("X-Powered-By");
    header_remove("Server");
}

// Call this function at the start of your application
setSecurityHeaders();
