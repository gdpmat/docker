<?php include_once __DIR__ . '/../includes/auth.php'; // JWT version ?>

<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$conn = getDbConnection();

try {
    // Get all sites
    $stmt = $conn->query("SELECT DISTINCT site_name FROM crawler_posts ORDER BY site_name");
    $sites = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    echo json_encode([
        'success' => true,
        'data' => $sites
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการดึงรายชื่อเว็บไซต์']);
    error_log("Sites error: " . $e->getMessage());
}
