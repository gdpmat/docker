<?php
// File: includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
  <div class="position-sticky pt-3">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
          <i class="bi bi-speedometer2"></i>
          แดชบอร์ด
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $currentPage === 'posts.php' ? 'active' : ''; ?>" href="posts.php">
          <i class="bi bi-card-list"></i>
          รายการโพสต์ทั้งหมด
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo $currentPage === 'search-replace.php' ? 'active' : ''; ?>"
          href="search-replace.php">
          <i class="bi bi-search"></i>
          ค้นหาและแทนที่
        </a>
      </li>
    </ul>

    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
      <span>รายงาน</span>
    </h6>
    <ul class="nav flex-column mb-2">
      <li class="nav-item">
        <a class="nav-link" href="api/posts.php" target="_blank">
          <i class="bi bi-file-earmark-code"></i>
          API ข้อมูลทั้งหมด
        </a>
      </li>
    </ul>
  </div>
</nav>