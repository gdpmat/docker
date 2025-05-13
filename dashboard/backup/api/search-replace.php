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
if (!isset($data['search']) || !isset($data['replace'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุคำค้นหาและคำที่ต้องการแทนที่']);
    exit;
}

$search = $data['search'];
$replace = $data['replace'];
$fields = isset($data['fields']) ? $data['fields'] : ['title', 'description', 'tags'];

// Validate fields
$allowedFields = ['title', 'description', 'url', 'site_name', 'tags'];
$validFields = array_intersect($fields, $allowedFields);

if (empty($validFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ไม่มีฟิลด์ที่ถูกต้องสำหรับการแทนที่']);
    exit;
}

$conn = getDbConnection();
$conn->beginTransaction();

try {
    $totalReplaced = 0;
    
    foreach ($validFields as $field) {
        $query = "UPDATE crawler_posts SET $field = REPLACE($field, ?, ?) WHERE $field LIKE ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$search, $replace, "%$search%"]);
        $totalReplaced += $stmt->rowCount();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "แทนที่คำค้นหา '$search' ด้วย '$replace' เรียบร้อย",
        'count' => $totalReplaced
    ]);
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการแทนที่ข้อมูล']);
    error_log("Search and replace error: " . $e->getMessage());
}
