<?php
// Stripe Configuration
return [
    'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: 'your_live_publishable_key_here',
    'secret_key' => getenv('STRIPE_SECRET_KEY') ?: 'your_live_secret_key_here',
    'mode' => 'live' // Set to 'live' for production
];