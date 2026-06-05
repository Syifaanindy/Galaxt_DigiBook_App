<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/midtrans-helper.php';
require_once __DIR__ . '/../models/transaksi-model.php';

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload tidak valid.']);
    exit;
}

if (!midtrans_signature_is_valid($payload)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Signature Midtrans tidak valid.']);
    exit;
}

$transactionCode = $payload['order_id'] ?? '';
$statusBaru = midtrans_map_status(
    $payload['transaction_status'] ?? 'pending',
    $payload['fraud_status'] ?? null
);

try {
    updateTransaksiDariStatusMidtrans($conn, $transactionCode, $statusBaru, $payload);
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
