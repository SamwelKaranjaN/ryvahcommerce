<?php
// PayPal Configuration
return [
    'client_id' => getenv('PAYPAL_CLIENT_ID') ?: 'AdX3JqQ4AkL0e_V6vPL0h_wiislS-3yzQKWjBgeecSi8la_aogGHvntBgKOyE8IuNc6-baWmoRTVzraj',
    'secret' => getenv('PAYPAL_SECRET') ?: 'EN4tj6VjPG_xklgqby-d-PtdeIjVNBfDQFgvps2eQNNAzeavtbxXdU_uWVoFMnvbEVV9B_yCuLHYmpQh',
    'mode' => 'live' // Set to 'live' for production
];