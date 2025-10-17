<?php
// attendance_handler.php - terima data absen dari ESP8266
list($data, $rawBody) = json_req_body();

// Contoh: header HMAC dan device ID
$deviceId  = $_SERVER['HTTP_X_DEVICE_ID'] ?? null;
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? null;

if (!$deviceId || !$signature) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_headers']);
    exit;
}

// Ambil secret device dari DB
$stmt = $pdo->prepare("SELECT id, secret_key FROM devices WHERE device_id = :did");
$stmt->execute([':did' => $deviceId]);
$device = $stmt->fetch();

if (!$device) {
    http_response_code(403);
    echo json_encode(['error' => 'unknown_device']);
    exit;
}

// Validasi HMAC
$computed = hash_hmac('sha256', $rawBody, $device['secret_key']);
if (!hash_equals($computed, $signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid_signature']);
    exit;
}

// Cek duplicate berdasarkan client_event_id (optional)
$client_event_id = $data['client_event_id'] ?? null;
if ($client_event_id) {
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE client_event_id=:ceid");
    $stmt->execute([':ceid' => $client_event_id]);
    if ($stmt->fetch()) {
        echo json_encode(['saved' => false, 'reason' => 'duplicate']);
        exit;
    }
}

// Simpan data absen
$ts             = $data['timestamp'] ?? date('c');
$fingerprint_id = $data['fingerprint_id'] ?? null;
$status         = $data['status'] ?? 'IN';
$meta           = isset($data['meta']) ? json_encode($data['meta']) : null;

$ins = $pdo->prepare("INSERT INTO attendance (client_event_id, device_id, fingerprint_id, status, timestamp, meta) VALUES (:ceid,:device_id,:fid,:status,:ts,:meta)");
$ins->execute([
    ':ceid'      => $client_event_id,
    ':device_id' => $device['id'],
    ':fid'       => $fingerprint_id,
    ':status'    => $status,
    ':ts'        => $ts,
    ':meta'      => $meta
]);

echo json_encode(['saved' => true, 'attendance_id' => $pdo->lastInsertId()]);
