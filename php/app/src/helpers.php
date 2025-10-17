<?php
// helpers.php - fungsi utilitas umum

/**
 * Kirim response JSON dan exit
 */
function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Ambil body JSON dari request dan decode
 * @return array [decoded array, raw body]
 */
function json_req_body(): array {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        json_response(['error' => 'invalid_json'], 400);
    }
    return [$data, $body];
}

/**
 * Validasi HMAC signature
 * @param string $payload  - body request
 * @param string $secret   - secret key device
 * @param string $signature - signature dari header
 * @return bool
 */
function validate_hmac(string $payload, string $secret, string $signature): bool {
    $computed = hash_hmac('sha256', $payload, $secret);
    return hash_equals($computed, $signature);
}

/**
 * Fungsi debug (opsional)
 */
function debug($var): void {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    exit;
}
