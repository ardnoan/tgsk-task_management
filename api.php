<?php
header('Content-Type: application/json');
require_once 'config.php';

// Cek apakah user sudah login untuk semua request kecuali login
$action = $_GET['action'] ?? '';
if ($action !== 'login' && !isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

switch ($action) {
    case 'get_users':
        $result = $conn->query("SELECT user_id, username, role FROM users ORDER BY username");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
        break;

    case 'get_tasks':
        // Admin bisa lihat semua task, user hanya miliknya
        if ($_SESSION['role'] === 'admin') {
            $result = $conn->query("SELECT t.*, u.username FROM tasks t JOIN users u ON t.user_id = u.user_id ORDER BY t.due_date DESC");
        } else {
            $user_id = $_SESSION['user_id'];
            $result = $conn->query("SELECT t.*, u.username FROM tasks t JOIN users u ON t.user_id = u.user_id WHERE t.user_id = $user_id ORDER BY t.due_date DESC");
        }
        
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        echo json_encode($tasks);
        break;

    case 'add_task':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validasi input
        if (empty($data['user_id']) || empty($data['title']) || empty($data['due_date'])) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, due_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", 
            $data['user_id'], 
            $data['title'], 
            $data['description'], 
            $data['due_date'], 
            $data['status']
        );
        
        if ($stmt->execute()) {
            $task_id = $stmt->insert_id;
            // Log aktivitas pembuatan task
            logUserActivity($conn, $_SESSION['user_id'], 'create_task');
            echo json_encode(['success' => true, 'task_id' => $task_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'update_task_status':
        $data = json_decode(file_get_contents('php://input'), true);
        $task_id = $data['task_id'];
        $new_status = $data['status'];

        // Dapatkan status lama
        $stmt = $conn->prepare("SELECT status FROM tasks WHERE task_id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_status = $result->fetch_assoc()['status'];
        $stmt->close();

        // Update status task
        $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE task_id = ?");
        $stmt->bind_param("si", $new_status, $task_id);
        
        if ($stmt->execute()) {
            // Log perubahan status
            $log_stmt = $conn->prepare("INSERT INTO task_status_logs (task_id, old_status, new_status) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $task_id, $old_status, $new_status);
            $log_stmt->execute();
            $log_stmt->close();
            
            // Log aktivitas user
            logUserActivity($conn, $_SESSION['user_id'], 'update_task');
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete_task':
        $task_id = $_GET['task_id'];
        
        // Cek apakah user berhak menghapus task ini
        if ($_SESSION['role'] !== 'admin') {
            $stmt = $conn->prepare("SELECT user_id FROM tasks WHERE task_id = ?");
            $stmt->bind_param("i", $task_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $task = $result->fetch_assoc();
            $stmt->close();
            
            if ($task['user_id'] !== $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                break;
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = ?");
        $stmt->bind_param("i", $task_id);
        
        if ($stmt->execute()) {
            logUserActivity($conn, $_SESSION['user_id'], 'delete_task');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'get_activities':
        // User hanya bisa lihat aktivitasnya sendiri, admin bisa lihat semua
        if ($_SESSION['role'] === 'admin') {
            $result = $conn->query("SELECT a.*, u.username FROM activities a JOIN users u ON a.user_id = u.user_id ORDER BY a.activity_date DESC");
        } else {
            $user_id = $_SESSION['user_id'];
            $result = $conn->query("SELECT a.*, u.username FROM activities a JOIN users u ON a.user_id = u.user_id WHERE a.user_id = $user_id ORDER BY a.activity_date DESC");
        }
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        echo json_encode($activities);
        break;

    case 'add_activity':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['activity_date']) || empty($data['activity_desc'])) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            break;
        }
        
        $stmt = $conn->prepare("INSERT INTO activities (user_id, activity_date, activity_desc) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", 
            $_SESSION['user_id'],
            $data['activity_date'], 
            $data['activity_desc']
        );
        
        if ($stmt->execute()) {
            logUserActivity($conn, $_SESSION['user_id'], 'add_activity');
            echo json_encode(['success' => true, 'activity_id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete_activity':
        $activity_id = $_GET['activity_id'];
        
        // Cek apakah user berhak menghapus aktivitas ini
        if ($_SESSION['role'] !== 'admin') {
            $stmt = $conn->prepare("SELECT user_id FROM activities WHERE activity_id = ?");
            $stmt->bind_param("i", $activity_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $activity = $result->fetch_assoc();
            $stmt->close();
            
            if ($activity['user_id'] !== $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                break;
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM activities WHERE activity_id = ?");
        $stmt->bind_param("i", $activity_id);
        
        if ($stmt->execute()) {
            logUserActivity($conn, $_SESSION['user_id'], 'delete_activity');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'get_task_logs':
        // Hanya admin yang bisa mengakses logs
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            break;
        }
        
        $result = $conn->query("
            SELECT tsl.*, t.title 
            FROM task_status_logs tsl 
            JOIN tasks t ON tsl.task_id = t.task_id 
            ORDER BY tsl.task_log_id DESC 
            LIMIT 20
        ");
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        echo json_encode($logs);
        break;

    case 'get_user_logs':
        // Hanya admin yang bisa mengakses logs
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            break;
        }
        
        $result = $conn->query("
            SELECT ul.*, u.username 
            FROM user_logs ul 
            JOIN users u ON ul.user_id = u.user_id 
            ORDER BY ul.activity_time DESC 
            LIMIT 20
        ");
        
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        echo json_encode($logs);
        break;

    case 'get_dashboard_stats':
        $user_id = $_SESSION['user_id'];
        $stats = [];
        
        if ($_SESSION['role'] === 'admin') {
            // Admin bisa lihat semua statistik
            $result = $conn->query("SELECT COUNT(*) as total FROM tasks");
            $stats['total_tasks'] = $result->fetch_assoc()['total'];
            
            $result = $conn->query("SELECT COUNT(*) as completed FROM tasks WHERE status = 'completed'");
            $stats['completed_tasks'] = $result->fetch_assoc()['completed'];
            
            $result = $conn->query("SELECT COUNT(*) as pending FROM tasks WHERE status = 'pending'");
            $stats['pending_tasks'] = $result->fetch_assoc()['pending'];
            
            $result = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'user'");
            $stats['total_users'] = $result->fetch_assoc()['total_users'];
        } else {
            // User hanya bisa lihat statistik miliknya
            $result = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE user_id = $user_id");
            $stats['total_tasks'] = $result->fetch_assoc()['total'];
            
            $result = $conn->query("SELECT COUNT(*) as completed FROM tasks WHERE user_id = $user_id AND status = 'completed'");
            $stats['completed_tasks'] = $result->fetch_assoc()['completed'];
            
            $result = $conn->query("SELECT COUNT(*) as pending FROM tasks WHERE user_id = $user_id AND status = 'pending'");
            $stats['pending_tasks'] = $result->fetch_assoc()['pending'];
        }
        
        echo json_encode($stats);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

$conn->close();
?>