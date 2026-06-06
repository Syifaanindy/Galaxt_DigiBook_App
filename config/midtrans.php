<?php

return [
    // Ganti dengan key dari dashboard Midtrans Sandbox.
    // Server key wajib tetap di backend. Jangan taruh server key di JavaScript.
    'server_key' => getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-H9M7kVhHyX-Shd3g6UhpNie_',
    'client_key' => getenv('MIDTRANS_CLIENT_KEY') ?: 'SB-Mid-client-XJHuCCBxP7A-BjFw',
    'is_production' => false,

    // Isi jika URL aplikasi sudah public/HTTPS
    'notification_url' => getenv('MIDTRANS_NOTIFICATION_URL') ?: '',
];

