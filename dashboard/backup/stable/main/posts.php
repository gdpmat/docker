<?php
// File: posts.php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/db.php';

$conn = getDbConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$siteFilter = isset($_GET['site']) ? $_GET['site'] : '';

// Build query
$query = "SELECT SQL_CALC_FOUND_ROWS * FROM crawler_posts WHERE 1=1";
$params = [];

if (!empty($search)) {
  $query .= " AND (title LIKE ? OR description LIKE ? OR tags LIKE ?)";
  $searchParam = "%$search%";
  $params[] = $searchParam;
  $params[] = $searchParam;
  $params[] = $searchParam;
}

if (!empty($siteFilter)) {
  $query .= " AND site_name = ?";
  $params[] = $siteFilter;
}

$query .= " ORDER BY created_at DESC LIMIT $offset, $perPage";

// Execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get total count
$stmt = $conn->query("SELECT FOUND_ROWS() as total");
$totalCount = $stmt->fetch()['total'];
$totalPages = ceil($totalCount / $perPage);

// Get site list for filter dropdown
$stmt = $conn->query("SELECT DISTINCT site_name FROM crawler_posts ORDER BY site_name");
$sites = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">รายการโพสต์ทั้งหมด</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group me-2">
            <a href="posts.php" class="btn btn-sm btn-outline-secondary">รีเฟรช</a>
          </div>
        </div>
      </div>

      <!-- Search form -->
      <div class="card mb-4">
        <div class="card-body">
          <form method="GET" action="posts.php" class="row g-3">
            <div class="col-md-6">
              <input type="text" class="form-control" name="search" placeholder="ค้นหา..."
                value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-4">
              <select class="form-select" name="site">
                <option value="">-- ทุกเว็บไซต์ --</option>
                <?php foreach ($sites as $site): ?>
                <option value="<?php echo htmlspecialchars($site['site_name']); ?>"
                  <?php echo $siteFilter === $site['site_name'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($site['site_name']); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Posts table -->
      <div class="table-responsive mb-4">
        <table class="table table-striped table-hover table-sm">
          <thead>
            <tr>
              <th>ID</th>
              <th>Video ID</th>
              <th>รูปภาพ</th>
              <th>ชื่อเรื่อง</th>
              <th>เว็บไซต์</th>
              <th>วันที่</th>
              <th>การจัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($posts)): ?>
            <tr>
              <td colspan="7" class="text-center py-4">ไม่พบข้อมูล</td>
            </tr>
            <?php else: ?>
            <?php foreach ($posts as $post): ?>
            <tr>
              <td><?php echo $post['id']; ?></td>
              <td><?php echo htmlspecialchars($post['video_id']); ?></td>
              <td>
                <?php if (!empty($post['thumbnail_url'])): ?>
                <img src="<?php echo htmlspecialchars($post['thumbnail_url']); ?>" alt="Thumbnail" class="img-thumbnail"
                  style="max-width: 80px;">
                <?php else: ?>
                <span class="text-muted">ไม่มีรูปภาพ</span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($post['title']); ?></td>
              <td><?php echo htmlspecialchars($post['site_name']); ?></td>
              <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
              <td>
                <a href="post-view.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info">ดู</a>
                <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                <button type="button" class="btn btn-sm btn-danger delete-post"
                  data-id="<?php echo $post['id']; ?>">ลบ</button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
          <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link"
              href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&site=<?php echo urlencode($siteFilter); ?>"
              aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>

          <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
          <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
            <a class="page-link"
              href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&site=<?php echo urlencode($siteFilter); ?>">
              <?php echo $i; ?>
            </a>
          </li>
          <?php endfor; ?>

          <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link"
              href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&site=<?php echo urlencode($siteFilter); ?>"
              aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">ยืนยันการลบ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        คุณต้องการลบโพสต์นี้ใช่หรือไม่? การดำเนินการนี้ไม่สามารถเรียกคืนได้
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">ลบ</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Delete post functionality
  const deleteButtons = document.querySelectorAll('.delete-post');
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
  const confirmDeleteBtn = document.getElementById('confirmDelete');
  let postIdToDelete = null;

  deleteButtons.forEach(button => {
    button.addEventListener('click', function() {
      postIdToDelete = this.getAttribute('data-id');
      deleteModal.show();
    });
  });

  confirmDeleteBtn.addEventListener('click', function() {
    if (postIdToDelete) {
      // Send delete request
      fetch(`api/posts.php?id=${postIdToDelete}`, {
          method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Reload page to refresh the list
            window.location.reload();
          } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('เกิดข้อผิดพลาดในการลบโพสต์');
        });

      deleteModal.hide();
    }
  });
});
</script>

<?php include 'includes/footer.php'; ?>