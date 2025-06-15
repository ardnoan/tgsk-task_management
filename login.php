<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (!empty($username) && !empty($password)) {
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();
      // Dalam sistem real, gunakan password_hash() dan password_verify()
      if ($password === $user['password']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Log aktivitas login
        logUserActivity($conn, $user['user_id'], 'login');

        header('Location: index.php');
        exit();
      } else {
        $error = 'Invalid username or password';
      }
    } else {
      $error = 'Invalid username or password';
    }
    $stmt->close();
  } else {
    $error = 'Please fill in all fields';
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Task Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">📌 Task Manager</h1>
      <p class="text-gray-600">Sign in to your account</p>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
        <input type="text" id="username" name="username" required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="Enter your username">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
        <input type="password" id="password" name="password" required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="Enter your password">
      </div>

      <button type="submit"
        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
        Sign In
      </button>
    </form>

    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        Demo accounts: admin1/admin123, user1/password1
      </p>
    </div>
  </div>
</body>

</html>