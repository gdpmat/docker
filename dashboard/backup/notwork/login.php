<?php
require_once __DIR__ . '/config/db.php';
session_start();

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
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user'] = $user['username'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
?>
