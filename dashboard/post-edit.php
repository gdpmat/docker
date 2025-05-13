<?php
// File: post-edit.php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/db.php';

if (!isset($_GET['id'])) {
  header('Location: posts.php');
  exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM crawler_posts WHERE id = ?");
$stmt->execute([$_GET['id']]);
$post = $stmt->fetch();

if (!$post) {
  header('Location: posts.php');
  exit;
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Fields that can be updated
  $fields = ['title', 'description', 'url', 'site_name', 'thumbnail_url', 'embedURL', 'tags'];
  $updates = [];
  $params = [];

  foreach ($fields as $field) {
    if (isset($_POST[$field])) {
      $updates[] = "$field = ?";
      $params[] = $_POST[$field];
    }
  }

  if (!empty($updates)) {
    // Add ID to params
    $params[] = $_GET['id'];

    try {
      $query = "UPDATE crawler_posts SET " . implode(', ', $updates) . " WHERE id = ?";
      $stmt = $conn->prepare($query);
      $result = $stmt->execute($params);

      if ($result) {
        $message = 'บันทึกข้อมูลเรียบร้อยแล้ว';
        $messageType = 'success';

        // Refresh post data
        $stmt = $conn->prepare("SELECT * FROM crawler_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $post = $stmt->fetch();
      } else {
        $message = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
        $messageType = 'danger';
      }
    } catch (PDOException $e) {
      $message = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
      $messageType = 'danger';
      error_log("Update post error: " . $e->getMessage());
    }
  }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">แก้ไขโพสต์</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group me-2">
            <a href="post-view.php?id=<?php echo $post['id']; ?>"
              class="btn btn-sm btn-outline-secondary">ดูรายละเอียด</a>
            <a href="posts.php" class="btn btn-sm btn-outline-secondary">กลับไปยังรายการ</a>
          </div>
        </div>
      </div>

      <?php if (!empty($message)): ?>
      <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="card mb-4">
        <div class="card-header">
          <h4>แก้ไขข้อมูลโพสต์ #<?php echo $post['id']; ?></h4>
        </div>
        <div class="card-body">
          <form method="POST" action="" class="row g-3">
            <div class="col-md-6">
              <label for="video_id" class="form-label">Video ID</label>
              <input type="text" class="form-control" id="video_id"
                value="<?php echo htmlspecialchars($post['video_id']); ?>" readonly>
              <div class="form-text">ไม่สามารถแก้ไข Video ID ได้</div>
            </div>

            <div class="col-md-6">
              <label for="site_name" class="form-label">เว็บไซต์</label>
              <input type="text" class="form-control" id="site_name" name="site_name"
                value="<?php echo htmlspecialchars($post['site_name']); ?>">
            </div>

            <div class="col-12">
              <label for="title" class="form-label">ชื่อเรื่อง</label>
              <input type="text" class="form-control" id="title" name="title"
                value="<?php echo htmlspecialchars($post['title']); ?>">
            </div>

            <div class="col-12">
              <label for="description" class="form-label">คำอธิบาย</label>
              <textarea class="form-control" id="description" name="description"
                rows="5"><?php echo htmlspecialchars($post['description']); ?></textarea>
            </div>

            <div class="col-12">
              <label for="url" class="form-label">URL</label>
              <input type="url" class="form-control" id="url" name="url"
                value="<?php echo htmlspecialchars($post['url']); ?>">
            </div>

            <div class="col-md-6">
              <label for="thumbnail_url" class="form-label">URL รูปภาพ</label>
              <input type="url" class="form-control" id="thumbnail_url" name="thumbnail_url"
                value="<?php echo htmlspecialchars($post['thumbnail_url']); ?>">
            </div>

            <div class="col-md-6">
              <label for="embedURL" class="form-label">URL สำหรับฝังวิดีโอ</label>
              <input type="url" class="form-control" id="embedURL" name="embedURL"
                value="<?php echo htmlspecialchars($post['embedURL']); ?>">
            </div>

            <div class="col-12">
              <label for="tags" class="form-label">แท็ก (คั่นด้วยเครื่องหมายจุลภาค)</label>
              <input type="text" class="form-control" id="tags" name="tags"
                value="<?php echo htmlspecialchars($post['tags']); ?>">
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
              <a href="post-view.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
          </form>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-6">
          <!-- Preview thumbnail -->
          <div class="card">
            <div class="card-header">
              <h5>ตัวอย่างรูปภาพ</h5>
            </div>
            <div class="card-body text-center">
              <div id="thumbnailPreview">
                <?php if (!empty($post['thumbnail_url'])): ?>
                <img src="<?php echo htmlspecialchars($post['thumbnail_url']); ?>" alt="Thumbnail" class="img-fluid">
                <?php else: ?>
                <div class="alert alert-secondary">ไม่มีรูปภาพ</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <!-- Preview embed -->
          <div class="card">
            <div class="card-header">
              <h5>ตัวอย่างวิดีโอ</h5>
            </div>
            <div class="card-body text-center">
              <div id="embedPreview">
                <?php if (!empty($post['embedURL'])): ?>
                <div class="ratio ratio-16x9">
                  <iframe src="<?php echo htmlspecialchars($post['embedURL']); ?>" allowfullscreen></iframe>
                </div>
                <?php else: ?>
                <div class="alert alert-secondary">ไม่มีวิดีโอ</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Live preview for thumbnail
  const thumbnailUrl = document.getElementById('thumbnail_url');
  const thumbnailPreview = document.getElementById('thumbnailPreview');

  thumbnailUrl.addEventListener('input', function() {
    if (this.value) {
      thumbnailPreview.innerHTML = `<img src="${this.value}" alt="Thumbnail" class="img-fluid">`;
    } else {
      thumbnailPreview.innerHTML = `<div class="alert alert-secondary">ไม่มีรูปภาพ</div>`;
    }
  });

  // Live preview for embed
  const embedURL = document.getElementById('embedURL');
  const embedPreview = document.getElementById('embedPreview');

  embedURL.addEventListener('input', function() {
    if (this.value) {
      embedPreview.innerHTML = `
                <div class="ratio ratio-16x9">
                    <iframe src="${this.value}" allowfullscreen></iframe>
                </div>
            `;
    } else {
      embedPreview.innerHTML = `<div class="alert alert-secondary">ไม่มีวิดีโอ</div>`;
    }
  });
});
</script>

<?php include 'includes/footer.php'; ?>