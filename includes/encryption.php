<?php

/**
 * Generate a random salt for password encryption
 * @return string
 */
function generate_salt()
{
    return bin2hex(random_bytes(16));
}

/**
 * Generate a random initialization vector for encryption
 * @return string
 */
function generate_iv()
{
    return bin2hex(random_bytes(16));
}

/**
 * Encrypt a password using AES-256-CBC
 * @param string $password The password to encrypt
 * @param string $salt The salt to use
 * @param string $iv The initialization vector
 * @return string The encrypted password
 */
function encrypt_password($password, $salt, $iv)
{
    $key = hash('sha256', $salt . getenv('ENCRYPTION_KEY'), true);
    $encrypted = openssl_encrypt(
        $password,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        hex2bin($iv)
    );
    return base64_encode($encrypted);
}

/**
 * Decrypt a password using AES-256-CBC
 * @param string $password The password to decrypt
 * @param string $salt The salt used for encryption
 * @param string $iv The initialization vector used for encryption
 * @return string The decrypted password
 */
function decrypt_password($password, $salt, $iv)
{
    $key = hash('sha256', $salt . getenv('ENCRYPTION_KEY'), true);
    $decrypted = openssl_decrypt(
        base64_decode($password),
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        hex2bin($iv)
    );
    return $decrypted;
}
