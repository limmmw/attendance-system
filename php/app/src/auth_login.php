<?php
// auth_login.php - login admin
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

list($data, ) = json_req_body();

$email    = $data['email'] ?? '';
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT id, password_hash, name FROM admins WHERE email=:email");
$stmt->execute([':email' => $email]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'invalid_credentials']);
    exit;
}

// Generate JWT
$now = time();
$payload = [
    'iss' => 'attendance-server',
    'iat' => $now,
    'exp' => $now + 3600, // 1 jam
    'sub' => $admin['id'],
    'name' => $admin['name']
];

$jwt = JWT::encode($payload, $config['jwt_secret'], 'HS256');
echo json_encode(['token' => $jwt]);
