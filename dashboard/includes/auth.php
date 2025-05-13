<?php
// File: includes/auth.php
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
function isLoggedIn()
{
  return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

// ตรวจสอบการล็อกอิน หากไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้าล็อกอิน
function requireLogin()
{
  if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
  }
}