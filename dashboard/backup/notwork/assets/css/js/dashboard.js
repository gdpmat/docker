// File: assets/js/dashboard.js
document.addEventListener("DOMContentLoaded", function () {
  // Enable tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Auto hide alerts after 5 seconds
  setTimeout(function () {
    const alerts = document.querySelectorAll(".alert-dismissible");
    alerts.forEach(function (alert) {
      bootstrap.Alert.getInstance(alert)?.close();
    });
  }, 5000);

  // Confirm delete actions
  const deleteButtons = document.querySelectorAll("[data-confirm]");
  deleteButtons.forEach(function (button) {
    button.addEventListener("click", function (e) {
      if (!confirm(this.dataset.confirm || "คุณแน่ใจหรือไม่?")) {
        e.preventDefault();
      }
    });
  });
});
