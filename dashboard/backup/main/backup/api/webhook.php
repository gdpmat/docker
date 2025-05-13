<?php include_once __DIR__ . '/../includes/auth.php'; ?>

<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['url']) || empty($data['url'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุ URL ที่ต้องการเก็บข้อมูล']);
    exit;
}

// Log the request
error_log("Received webhook request for URL: " . $data['url']);

// Call n8n webhook
$url = 'https://n8n.ngin.cc/webhook/scraping-data';
$payload = json_encode(['url' => $data['url']]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
]);

$result = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    error_log("Curl error: " . curl_error($ch));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการส่งคำขอไปยัง webhook']);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Forward response from n8n
http_response_code($statusCode);
echo $result;
