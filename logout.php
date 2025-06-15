<?php
require_once 'config.php';

if (isLoggedIn()) {
  // Log aktivitas logout
  logUserActivity($conn, $_SESSION['user_id'], 'logout');

  // Destroy session
  session_destroy();
}

// Redirect ke login page
header('Location: login.php');
exit();
