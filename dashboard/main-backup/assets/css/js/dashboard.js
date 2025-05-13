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

// Attach to window for global access
window.extractVideo = function () {
  const iframeUrl = document.getElementById("iframe_url").value;
  const videoId = document.getElementById("video_id").value;
  if (!iframeUrl || !videoId) {
    Swal.fire("Missing Input", "Please enter both iframe URL and video ID.", "warning");
    return;
  }

  Swal.fire({
    title: "Processing...",
    html: "Extracting and downloading video. Please wait...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch("http://localhost:8010/extract-video", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ iframe_url: iframeUrl, video_id: videoId })
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === "success") {
      Swal.fire("Downloaded!", `File: ${data.filename} | Duration: ${data.duration}`, "success");
    } else {
      Swal.fire("Error", data.detail || "An error occurred", "error");
    }
  })
  .catch(error => {
    Swal.fire("Error", error.message || "An error occurred", "error");
  });
};
