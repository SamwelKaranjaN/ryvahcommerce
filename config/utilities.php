<?php
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price) {
        return number_format($price, 2, '.', '');
    }
}

if (!function_exists('validatePhoneNumber')) {
    function validatePhoneNumber($phone) {
        // Remove all non-numeric characters except + for international format
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Check if it's an international number (starts with +)
        if (strpos($phone, '+') === 0) {
            // International number should be between 8 and 15 digits
            return strlen($phone) >= 8 && strlen($phone) <= 15;
        }
        
        // For local numbers, should be between 10 and 15 digits
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
}

if (!function_exists('formatPhoneNumber')) {
    function formatPhoneNumber($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Format based on length
        if (strlen($phone) === 17) {
            // Format as (XXX) XXX-XXXX
            return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
        } else if (strlen($phone) === 18 && substr($phone, 0, 1) === '1') {
            // Format as 1 (XXX) XXX-XXXX
            return '1 (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7);
        }
        
        // Return original if not matching standard formats
        return $phone;
    }
}

if (!function_exists('validateZipCode')) {
    function validateZipCode($zip) {
        return preg_match('/^\d{5}(-\d{4})?$/', $zip);
    }
}
?> 