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
    // Get total posts
    $stmt = $conn->query("SELECT COUNT(*) as total FROM crawler_posts");
    $totalPosts = $stmt->fetch()['total'];
    
    // Get total sites
    $stmt = $conn->query("SELECT COUNT(DISTINCT site_name) as total FROM crawler_posts");
    $totalSites = $stmt->fetch()['total'];
    
    // Get recent posts (7 days)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM crawler_posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentPosts = $stmt->fetch()['total'];
    
    // Get top sites
    $stmt = $conn->query("SELECT site_name, COUNT(*) as count FROM crawler_posts GROUP BY site_name ORDER BY count DESC LIMIT 5");
    $topSites = $stmt->fetchAll();
    
    // Get recent posts list
    $stmt = $conn->query("SELECT id, video_id, title, site_name, thumbnail_url, created_at FROM crawler_posts ORDER BY created_at DESC LIMIT 5");
    $recentPostsList = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_posts' => $totalPosts,
            'total_sites' => $totalSites,
            'recent_posts' => $recentPosts,
            'top_sites' => $topSites,
            'recent_posts_list' => $recentPostsList
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ']);
    error_log("Stats error: " . $e->getMessage());
}
