<?php
// bootstrap.php - inisialisasi DB, env, helper

require __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$config = [
    'db_host'    => getenv('DB_HOST') ?: 'db',
    'db_name'    => getenv('DB_NAME') ?: 'attendance',
    'db_user'    => getenv('DB_USER') ?: 'root',
    'db_pass'    => getenv('DB_PASS') ?: 'example',
    'jwt_secret' => getenv('JWT_SECRET') ?: 'super_secret_change_me',
    'redis_host' => getenv('REDIS_HOST') ?: 'redis'
];

// Koneksi PDO ke MariaDB
try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_connection_failed', 'message' => $e->getMessage()]);
    exit;
}

// Helper: ambil body JSON
function json_req_body(): array {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_json']);
        exit;
    }
    return [$data, $body];
}
