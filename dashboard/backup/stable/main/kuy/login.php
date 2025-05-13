<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/jwt_secret.php';
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['username'], $input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing credentials']);
    exit;
}

$username = $input['username'];
$password = $input['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    $payload = [
        'iss' => 'dashboard',
        'sub' => $user['username'],
        'iat' => time(),
        'exp' => time() + 3600
    ];
    $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
    echo json_encode(['success' => true, 'token' => $jwt]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
?>