<?php
require_once __DIR__ . '/../config/jwt_secret.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Missing Authorization header']);
    exit;
}

list(, $jwt) = explode(' ', $headers['Authorization'], 2);

try {
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));
    $user = $decoded->sub ?? null;
    if (!$user) {
        throw new Exception("Invalid token payload");
    }
    // $user now available
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'error' => $e->getMessage()]);
    exit;
}
?>