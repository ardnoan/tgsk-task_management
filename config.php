<?php
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'task_activity_management');

// Membuat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Set charset ke UTF-8
$conn->set_charset("utf8");

// Fungsi untuk log aktivitas user
function logUserActivity($conn, $user_id, $activity_type)
{
  $stmt = $conn->prepare("INSERT INTO user_logs (user_id, activity_type) VALUES (?, ?)");
  $stmt->bind_param("is", $user_id, $activity_type);
  $stmt->execute();
  $stmt->close();
}

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn()
{
  return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk redirect jika belum login
function requireLogin()
{
  if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
  }
}
