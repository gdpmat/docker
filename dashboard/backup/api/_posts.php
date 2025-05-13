<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$conn = getDbConnection();

// Handle GET request (list or single post)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if ID is provided (single post)
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM crawler_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $post = $stmt->fetch();

        if ($post) {
            $post['short_code'] = '[jwplayer_exoclick vid="' . $post['video_id'] . '"]';
            echo json_encode(['success' => true, 'data' => $post]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'ไม่พบโพสต์']);
        }
        exit;
    }

    // Check if video_id is provided
    if (isset($_GET['video_id'])) {
        $stmt = $conn->prepare("SELECT * FROM crawler_posts WHERE video_id = ?");
        $stmt->execute([$_GET['video_id']]);
        $post = $stmt->fetch();

        if ($post) {
            $post['short_code'] = '[jwplayer_exoclick vid="' . $post['video_id'] . '"]';
            echo json_encode(['success' => true, 'data' => $post]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'ไม่พบโพสต์']);
        }
        exit;
    }

    // Handle list with pagination and filters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $perPage;

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $site = isset($_GET['site']) ? $_GET['site'] : '';
    $tag = isset($_GET['tag']) ? $_GET['tag'] : '';

    $query = "SELECT SQL_CALC_FOUND_ROWS * FROM crawler_posts WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR description LIKE ? OR tags LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($site)) {
        $query .= " AND site_name = ?";
        $params[] = $site;
    }

    if (!empty($tag)) {
        $query .= " AND tags LIKE ?";
        $params[] = "%$tag%";
    }

    $query .= " ORDER BY created_at DESC LIMIT $offset, $perPage";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    $stmt = $conn->query("SELECT FOUND_ROWS() as total");
    $totalCount = $stmt->fetch()['total'];
    $totalPages = ceil($totalCount / $perPage);

    echo json_encode([
        'success' => true,
        'data' => $posts,
        'pagination' => [
            'total' => $totalCount,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $totalPages
        ]
    ]);
    exit;
}

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Validate that ID is provided
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไม่ระบุ ID ที่ต้องการลบ']);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM crawler_posts WHERE id = ?");
        $result = $stmt->execute([$_GET['id']]);

        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อย']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลที่ต้องการลบ']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
        error_log("Delete post error: " . $e->getMessage());
    }

    exit;
}

// Handle POST request (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate that ID is provided
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไม่ระบุ ID ที่ต้องการแก้ไข']);
        exit;
    }

    // Fields that can be updated
    $allowedFields = ['title', 'description', 'url', 'site_name', 'thumbnail_url', 'embedURL', 'tags'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลที่ต้องการแก้ไข']);
        exit;
    }

    // Add ID to params
    $params[] = $data['id'];

    try {
        $query = "UPDATE crawler_posts SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            // Fetch updated post
            $stmt = $conn->prepare("SELECT * FROM crawler_posts WHERE id = ?");
            $stmt->execute([$data['id']]);
            $post = $stmt->fetch();

            echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อย', 'data' => $post]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล']);
        error_log("Update post error: " . $e->getMessage());
    }

    exit;
}

// Handle PUT request (create new post)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Required fields
    $requiredFields = ['video_id', 'title'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "กรุณาระบุ $field"]);
            exit;
        }
    }

    // Fields that can be inserted
    $fields = ['video_id', 'title', 'description', 'url', 'site_name', 'thumbnail_url', 'embedURL', 'tags'];
    $insertFields = [];
    $placeholders = [];
    $params = [];

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $insertFields[] = $field;
            $placeholders[] = '?';
            $params[] = $data[$field];
        }
    }

    try {
        $query = "INSERT INTO crawler_posts (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $conn->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            $newId = $conn->lastInsertId();

            // Fetch new post
            $stmt = $conn->prepare("SELECT * FROM crawler_posts WHERE id = ?");
            $stmt->execute([$newId]);
            $post = $stmt->fetch();

            echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อย', 'data' => $post]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล']);
        error_log("Insert post error: " . $e->getMessage());
    }

    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);