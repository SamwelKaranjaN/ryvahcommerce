<?php
return [
    'client_id' => 'ARb4izn3jwTWc2j2x6UDmompOiO2Uq3HQKodHTR3Y6UKUN61daJD09G8JVrx6UWz11-CL2fcty8UJ2CJ',
    'client_secret' => 'EHHv6Yf6p65iSR_MNUVp9JDgK0Ma81N7Bu3mX6Tt_k7VQpq2TIM626vYTkF5rHwzofdEHxBLMmkOLhqe',
    'mode' => 'sandbox', // 'sandbox' or 'live'
    'currency' => 'USD',
    'log_enabled' => true,
    'log_level' => 'DEBUG',
    'cache_enabled' => true,
    'cache_path' => __DIR__ . '/../cache',
    'http_proxy' => null,
    'http_timeout' => 30,
    'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/checkout/success.php',
    'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/checkout/cancel.php',
    'webhook_id' => '', // Set this in your environment configuration
    'webhook_secret' => '' // Set this in your environment configuration
];