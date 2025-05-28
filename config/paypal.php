<?php
// PayPal Configuration
return [
<<<<<<< Updated upstream
    'client_id' => getenv('PAYPAL_CLIENT_ID') ?: 'AdX3JqQ4AkL0e_V6vPL0h_wiislS-3yzQKWjBgeecSi8la_aogGHvntBgKOyE8IuNc6-baWmoRTVzraj',
    'secret' => getenv('PAYPAL_SECRET') ?: 'EN4tj6VjPG_xklgqby-d-PtdeIjVNBfDQFgvps2eQNNAzeavtbxXdU_uWVoFMnvbEVV9B_yCuLHYmpQh',
    'mode' => 'live' // Set to 'live' for production
];
=======
    'client_id' => getenv('PAYPAL_CLIENT_ID') ?: 'ARb4izn3jwTWc2j2x6UDmompOiO2Uq3HQKodHTR3Y6UKUN61daJD09G8JVrx6UWz11-CL2fcty8UJ2CJ',
    'secret' => getenv('PAYPAL_SECRET') ?: 'EHHv6Yf6p65iSR_MNUVp9JDgK0Ma81N7Bu3mX6Tt_k7VQpq2TIM626vYTkF5rHwzofdEHxBLMmkOLhqe',
    'mode' => 'sandbox', // Set to 'live' for production
    'business_email' => getenv('PAYPAL_BUSINESS_EMAIL') ?: 'samwelnjoroge757@gmail.com',
    'currency' => 'USD',
    'ipn_url' => 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr', // Use https://ipnpb.paypal.com/cgi-bin/webscr for production
    'api_url' => 'https://api-m.sandbox.paypal.com', // Use https://api-m.paypal.com for production
    'webhook_id' => getenv('PAYPAL_WEBHOOK_ID') ?: '', // Add your webhook ID for production
    'application_context' => [
        'brand_name' => 'Ryvah Commerce',
        'landing_page' => 'LOGIN', // Changed from NO_PREFERENCE to LOGIN to force login
        'user_action' => 'PAY_NOW',
        'return_url' => 'http://localhost/ryvahcommerce/checkout/payment_success.php',
        'cancel_url' => 'http://localhost/ryvahcommerce/checkout/payment_cancel.php'
    ]
];
>>>>>>> Stashed changes
