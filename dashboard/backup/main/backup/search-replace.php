<?php
// File: search-replace.php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/db.php';

$conn = getDbConnection();

// Get field options
$fields = [
  'title' => 'ชื่อเรื่อง',
  'description' => 'คำอธิบาย',
  'url' => 'URL',
  'site_name' => 'ชื่อเว็บไซต์',
  'tags' => 'แท็ก'
];
?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid">
  <div class="row">
    <?php include 'includes/sidebar.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">ค้นหาและแทนที่</h1>
      </div>

      <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i> ฟังก์ชันนี้ใช้สำหรับค้นหาและแทนที่ข้อความในฐานข้อมูล
        ใช้ด้วยความระมัดระวังเนื่องจากการแทนที่จะมีผลกับข้อมูลทั้งหมดที่ตรงกับเงื่อนไข
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h4>ค้นหาและแทนที่ข้อความ</h4>
        </div>
        <div class="card-body">
          <form id="searchReplaceForm" class="row g-3">
            <div class="col-md-6">
              <label for="searchText" class="form-label">ค้นหา</label>
              <input type="text" class="form-control" id="searchText" placeholder="คำหรือข้อความที่ต้องการค้นหา"
                required>
            </div>
            <div class="col-md-6">
              <label for="replaceText" class="form-label">แทนที่ด้วย</label>
              <input type="text" class="form-control" id="replaceText" placeholder="คำหรือข้อความที่ต้องการแทนที่">
            </div>

            <div class="col-12">
              <label class="form-label">เลือกฟิลด์ที่ต้องการค้นหาและแทนที่</label>
              <div class="row">
                <?php foreach ($fields as $key => $label): ?>
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="<?php echo $key; ?>"
                      id="field_<?php echo $key; ?>" name="fields[]" checked>
                    <label class="form-check-label" for="field_<?php echo $key; ?>">
                      <?php echo $label; ?>
                    </label>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-primary" id="submitBtn">ค้นหาและแทนที่</button>
            </div>
          </form>
        </div>
      </div>

      <div id="resultArea" class="mb-4 d-none">
        <div class="card">
          <div class="card-header">
            <h4>ผลลัพธ์</h4>
          </div>
          <div class="card-body" id="resultContent">
            <!-- Results will be displayed here -->
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">ยืนยันการแทนที่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmModalBody">
        คุณต้องการค้นหาและแทนที่ข้อความตามที่ระบุใช่หรือไม่?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="confirmReplace">ยืนยัน</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchReplaceForm = document.getElementById('searchReplaceForm');
  const searchText = document.getElementById('searchText');
  const replaceText = document.getElementById('replaceText');
  const resultArea = document.getElementById('resultArea');
  const resultContent = document.getElementById('resultContent');
  const submitBtn = document.getElementById('submitBtn');
  const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
  const confirmModalBody = document.getElementById('confirmModalBody');
  const confirmReplaceBtn = document.getElementById('confirmReplace');

  searchReplaceForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Get selected fields
    const selectedFields = [];
    document.querySelectorAll('input[name="fields[]"]:checked').forEach(field => {
      selectedFields.push(field.value);
    });

    if (selectedFields.length === 0) {
      alert('กรุณาเลือกอย่างน้อย 1 ฟิลด์');
      return;
    }

    // Update confirmation modal
    confirmModalBody.innerHTML = `
            <p>คุณต้องการค้นหา <strong>"${searchText.value}"</strong> และแทนที่ด้วย <strong>"${replaceText.value}"</strong> ในฟิลด์ต่อไปนี้:</p>
            <ul>
                ${selectedFields.map(field => `<li>${document.querySelector(`label[for="field_${field}"]`).textContent}</li>`).join('')}
            </ul>
            <p class="text-danger">คำเตือน: การดำเนินการนี้จะมีผลกับข้อมูลทั้งหมดที่ตรงกับเงื่อนไข และไม่สามารถเรียกคืนได้!</p>
        `;

    confirmModal.show();
  });

  confirmReplaceBtn.addEventListener('click', function() {
    // Prepare data
    const selectedFields = [];
    document.querySelectorAll('input[name="fields[]"]:checked').forEach(field => {
      selectedFields.push(field.value);
    });

    const data = {
      search: searchText.value,
      replace: replaceText.value,
      fields: selectedFields
    };

    // Disable button and show loading
    confirmReplaceBtn.disabled = true;
    confirmReplaceBtn.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังดำเนินการ...';

    // Send request
    fetch('api/search-replace.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(result => {
        confirmModal.hide();

        // Show result
        resultArea.classList.remove('d-none');
        if (result.success) {
          resultContent.innerHTML = `
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle"></i> ดำเนินการเรียบร้อย</h5>
                        <p>${result.message}</p>
                        <p>จำนวนข้อมูลที่ถูกแทนที่: <strong>${result.count}</strong> รายการ</p>
                    </div>
                `;
        } else {
          resultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="bi bi-exclamation-triangle"></i> เกิดข้อผิดพลาด</h5>
                        <p>${result.message}</p>
                    </div>
                `;
        }

        // Reset form if successful
        if (result.success) {
          searchText.value = '';
          replaceText.value = '';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        confirmModal.hide();

        resultArea.classList.remove('d-none');
        resultContent.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> เกิดข้อผิดพลาด</h5>
                    <p>ไม่สามารถดำเนินการได้ กรุณาลองใหม่อีกครั้ง</p>
                </div>
            `;
      })
      .finally(() => {
        // Re-enable button
        confirmReplaceBtn.disabled = false;
        confirmReplaceBtn.innerHTML = 'ยืนยัน';
      });
  });
});
</script>

<?php include 'includes/footer.php'; ?>