<?php

function midtrans_config() {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/midtrans.php';
    }
    return $config;
}

function midtrans_is_configured() {
    $config = midtrans_config();

    return !empty($config['server_key'])
        && !empty($config['client_key'])
        && strpos($config['server_key'], 'ISI_SERVER_KEY') !== 0
        && strpos($config['client_key'], 'ISI_CLIENT_KEY') !== 0;
}

function midtrans_base_urls() {
    $config = midtrans_config();

    if (!empty($config['is_production'])) {
        return [
            'snap_api' => 'https://app.midtrans.com/snap/v1/transactions',
            'snap_js' => 'https://app.midtrans.com/snap/snap.js',
            'status_api' => 'https://api.midtrans.com/v2/',
        ];
    }

    return [
        'snap_api' => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
        'snap_js' => 'https://app.sandbox.midtrans.com/snap/snap.js',
        'status_api' => 'https://api.sandbox.midtrans.com/v2/',
    ];
}

function midtrans_client_key() {
    $config = midtrans_config();
    return $config['client_key'];
}

function midtrans_snap_js_url() {
    $urls = midtrans_base_urls();
    return $urls['snap_js'];
}

function midtrans_notification_url() {
    $config = midtrans_config();
    return trim($config['notification_url'] ?? '');
}

function midtrans_request($method, $url, $payload = null, $extraHeaders = []) {
    $config = midtrans_config();
    $headers = array_merge([
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($config['server_key'] . ':'),
    ], $extraHeaders);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($payload !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $body = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curlError) {
        throw new Exception('Gagal menghubungi Midtrans: ' . $curlError);
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new Exception('Response Midtrans tidak valid.');
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $message = $data['error_messages'][0] ?? $data['status_message'] ?? 'Request Midtrans gagal.';
        throw new Exception($message);
    }

    return $data;
}

function midtrans_create_snap_transaction($payload) {
    $urls = midtrans_base_urls();
    $headers = [];
    $notificationUrl = midtrans_notification_url();

    if ($notificationUrl !== '') {
        $headers[] = 'X-Append-Notification: ' . $notificationUrl;
    }

    return midtrans_request('POST', $urls['snap_api'], $payload, $headers);
}

function midtrans_get_transaction_status($orderId) {
    $urls = midtrans_base_urls();
    return midtrans_request('GET', $urls['status_api'] . rawurlencode($orderId) . '/status');
}

function midtrans_signature_is_valid($payload) {
    $config = midtrans_config();
    $required = ['order_id', 'status_code', 'gross_amount', 'signature_key'];

    foreach ($required as $key) {
        if (!isset($payload[$key])) {
            return false;
        }
    }

    $signature = hash(
        'sha512',
        $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $config['server_key']
    );

    return hash_equals($signature, $payload['signature_key']);
}

function midtrans_map_status($transactionStatus, $fraudStatus = null) {
    $transactionStatus = strtolower((string) $transactionStatus);
    $fraudStatus = strtolower((string) $fraudStatus);

    if ($transactionStatus === 'settlement') {
        return 'success';
    }

    if ($transactionStatus === 'capture') {
        return ($fraudStatus === '' || $fraudStatus === 'accept') ? 'success' : 'failed';
    }

    if ($transactionStatus === 'pending') {
        return 'pending';
    }

    if (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true)) {
        return 'failed';
    }

    return 'pending';
}
