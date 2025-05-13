<?php
// File: post-view.php
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
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">รายละเอียดโพสต์</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group me-2">
            <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-secondary">แก้ไข</a>
            <a href="posts.php" class="btn btn-sm btn-outline-secondary">กลับไปยังรายการ</a>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-8">
          <div class="card mb-4">
            <div class="card-header">
              <h4><?php echo htmlspecialchars($post['title']); ?></h4>
            </div>
            <div class="card-body">
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">ID:</div>
                <div class="col-md-9"><?php echo $post['id']; ?></div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">Video ID:</div>
                <div class="col-md-9"><?php echo htmlspecialchars($post['video_id']); ?></div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">เว็บไซต์:</div>
                <div class="col-md-9"><?php echo htmlspecialchars($post['site_name']); ?></div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">URL:</div>
                <div class="col-md-9">
                  <a href="<?php echo htmlspecialchars($post['url']); ?>" target="_blank">
                    <?php echo htmlspecialchars($post['url']); ?>
                  </a>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">คำอธิบาย:</div>
                <div class="col-md-9"><?php echo nl2br(htmlspecialchars($post['description'])); ?></div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">แท็ก:</div>
                <div class="col-md-9">
                  <?php
                  $tags = explode(',', $post['tags']);
                  foreach ($tags as $tag) {
                    $tag = trim($tag);
                    if (!empty($tag)) {
                      echo '<span class="badge bg-secondary me-1 mb-1">' . htmlspecialchars($tag) . '</span>';
                    }
                  }
                  ?>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">วันที่สร้าง:</div>
                <div class="col-md-9"><?php echo date('d/m/Y H:i:s', strtotime($post['created_at'])); ?></div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3 fw-bold">วันที่อัปเดต:</div>
                <div class="col-md-9"><?php echo date('d/m/Y H:i:s', strtotime($post['updated_at'])); ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <!-- Thumbnail -->
          <div class="card mb-4">
            <div class="card-header">
              <h5>รูปภาพ</h5>
            </div>
            <div class="card-body text-center">
              <?php if (!empty($post['thumbnail_url'])): ?>
              <img src="<?php echo htmlspecialchars($post['thumbnail_url']); ?>" alt="Thumbnail" class="img-fluid mb-3">
              <a href="<?php echo htmlspecialchars($post['thumbnail_url']); ?>"
                download="<?php echo htmlspecialchars($post['video_id']); ?>.jpg" class="btn btn-primary">
                <i class="bi bi-download"></i> ดาวน์โหลด
              </a>
              <?php else: ?>
              <div class="alert alert-secondary">ไม่มีรูปภาพ</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Embed Video Section -->
      <?php if (!empty($post['embedURL'])): ?>
      <div class="card mb-4">
        <div class="card-header">
          <h5>วิดีโอ</h5>
        </div>
        <div class="card-body">
          <div class="ratio ratio-16x9">
            <iframe src="<?php echo htmlspecialchars($post['embedURL']); ?>" allowfullscreen></iframe>
          </div>
          <div class="mt-3">
            <p class="mb-2">Embed URL:</p>
            <div class="input-group">
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($post['embedURL']); ?>"
                readonly id="embedUrlInput">
              <button class="btn btn-outline-secondary" type="button" id="copyEmbedUrl">คัดลอก</button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Embed Code Section -->
      <div class="card mb-4">
        <div class="card-header">
          <h5>โค้ดสำหรับฝัง</h5>
        </div>
        <div class="card-body">
          <p class="mb-2">สามารถนำโค้ด HTML ด้านล่างนี้ไปใช้ในการฝังวิดีโอลงในเว็บไซต์ของคุณ:</p>
          <div class="input-group mb-3">
            <textarea class="form-control" rows="5" readonly
              id="embedCodeInput"><?php if (!empty($post['embedURL'])): ?><iframe src="<?php echo htmlspecialchars($post['embedURL']); ?>" width="100%" height="400" frameborder="0" allowfullscreen></iframe><?php endif; ?></textarea>
            <button class="btn btn-outline-secondary" type="button" id="copyEmbedCode">คัดลอก</button>
          </div>
        </div>
      </div>

      <!-- JSON API Example -->
      <div class="card mb-4">
        <div class="card-header">
          <h5>API Endpoints</h5>
        </div>
        <div class="card-body">
          <p class="mb-2">สามารถเข้าถึงข้อมูลโพสต์นี้ผ่าน API ได้ด้วย URL ต่อไปนี้:</p>
          <div class="input-group mb-3">
            <input type="text" class="form-control"
              value="<?php echo "https://" . $_SERVER['HTTP_HOST'] . "/api/posts.php?id=" . $post['id']; ?>" readonly
              id="apiUrlInput">
            <button class="btn btn-outline-secondary" type="button" id="copyApiUrl">คัดลอก</button>
          </div>
          <div class="input-group mb-3">
            <input type="text" class="form-control"
              value="<?php echo "https://" . $_SERVER['HTTP_HOST'] . "/api/posts.php?video_id=" . $post['video_id']; ?>"
              readonly id="apiVideoIdUrlInput">
            <button class="btn btn-outline-secondary" type="button" id="copyApiVideoIdUrl">คัดลอก</button>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Function to handle copy button clicks
  function setupCopyButton(buttonId, inputId) {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);

    if (button && input) {
      button.addEventListener('click', function() {
        input.select();
        document.execCommand('copy');

        // Change button text temporarily
        const originalText = button.innerText;
        button.innerText = 'คัดลอกแล้ว!';
        setTimeout(() => {
          button.innerText = originalText;
        }, 2000);
      });
    }
  }

  // Setup all copy buttons
  setupCopyButton('copyEmbedUrl', 'embedUrlInput');
  setupCopyButton('copyEmbedCode', 'embedCodeInput');
  setupCopyButton('copyApiUrl', 'apiUrlInput');
  setupCopyButton('copyApiVideoIdUrl', 'apiVideoIdUrlInput');
});
</script>

<?php include 'includes/footer.php'; ?>