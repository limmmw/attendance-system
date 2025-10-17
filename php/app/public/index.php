<?php
require __DIR__ . '/../src/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

switch (true) {

    case $uri === '/api/v1/attendance' && $method === 'POST':
        require __DIR__ . '/../src/attendance_handler.php';
        break;

    case $uri === '/api/v1/auth/login' && $method === 'POST':
        require __DIR__ . '/../src/auth_login.php';
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'not_found',
            'message' => 'Endpoint tidak ditemukan'
        ]);
        break;
}
