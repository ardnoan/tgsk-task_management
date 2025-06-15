# Task & Activity Management System Documentation

## 📋 Overview
Task & Activity Management System adalah aplikasi web untuk mengelola tugas dan aktivitas harian yang ditujukan untuk freelancer, pelajar, dan pekerja.

## 🔧 System Architecture

### Database Schema
```
task_activity_management/
├── users (Master data pengguna)
├── tasks (Data tugas dengan deadline)
├── activities (Catatan aktivitas harian)
├── task_status_logs (Log perubahan status tugas)
└── user_logs (Log aktivitas login/logout/aksi user)
```

### File Structure
```
project/
├── config.php (Konfigurasi database & fungsi helper)
├── login.php (Halaman login)
├── logout.php (Proses logout)
├── api.php (API endpoint untuk semua operasi)
└── index.php (Dashboard utama)
```

## 👥 User Roles & Permissions

### Admin Role
- ✅ Melihat semua tasks dari semua users
- ✅ Melihat semua activities dari semua users
- ✅ Akses ke semua logs (task logs & user logs)
- ✅ Statistik keseluruhan sistem
- ✅ Menghapus task/activity siapa saja

### User Role
- ✅ Melihat hanya tasks milik sendiri
- ✅ Melihat hanya activities milik sendiri
- ❌ Tidak bisa akses logs
- ✅ Statistik personal
- ✅ Hanya bisa hapus task/activity milik sendiri

## 🖱️ User Interface Guide

### Dashboard Buttons & Functions

#### 📊 Dashboard Stats (Tampil otomatis)
- **Total Tasks**: Jumlah semua tugas
- **Completed Tasks**: Jumlah tugas selesai
- **Pending Tasks**: Jumlah tugas pending
- **Total Users** (Admin only): Jumlah pengguna

#### 📝 Task Management Section
- **"Add New Task" Button**: Membuka form untuk membuat tugas baru
  - Input: User (dropdown), Title, Description, Due Date, Status
  - Process: Insert ke table `tasks` + log aktivitas
  - Transaction: `INSERT tasks` → `INSERT user_logs`

- **Status Dropdown** (pada setiap task):
  - Options: Pending, In Progress, Completed
  - Process: Update status + log perubahan
  - Transaction: `UPDATE tasks` → `INSERT task_status_logs` → `INSERT user_logs`

- **Delete Button** (🗑️):
  - Process: Hapus task + log aktivitas
  - Authorization: User hanya bisa hapus miliknya, Admin bisa hapus semua
  - Transaction: `DELETE tasks` → `INSERT user_logs`

#### 🎯 Activity Management Section
- **"Add New Activity" Button**: Membuka form untuk catat aktivitas
  - Input: Date, Description
  - Process: Insert ke table `activities`
  - Transaction: `INSERT activities` → `INSERT user_logs`

- **Delete Button** (🗑️):
  - Process: Hapus activity + log aktivitas
  - Authorization: User hanya bisa hapus miliknya, Admin bisa hapus semua
  - Transaction: `DELETE activities` → `INSERT user_logs`

#### 📊 Reports Section (Admin Only)
- **Task Status Logs**: Menampilkan 20 perubahan status terakhir
- **User Activity Logs**: Menampilkan 20 aktivitas user terakhir

### Navigation
- **Dashboard**: Halaman utama dengan statistik
- **Tasks**: Kelola tugas
- **Activities**: Kelola aktivitas harian
- **Reports**: Laporan dan logs (Admin only)
- **Logout**: Keluar dari sistem

## 🔄 Business Processes & Transactions

### 1. Login Process
```
Input: Username + Password
Process: Validasi user → Create session → Log login
Transaction: SELECT users → INSERT user_logs
```

### 2. Create Task Process
```
Input: User ID, Title, Description, Due Date, Status
Process: Insert task → Log user activity
Transaction: INSERT tasks → INSERT user_logs
```

### 3. Update Task Status Process
```
Input: Task ID, New Status
Process: Get old status → Update task → Log status change → Log user activity
Transaction: 
  SELECT tasks (get old status)
  → UPDATE tasks (new status)
  → INSERT task_status_logs
  → INSERT user_logs
```

### 4. Delete Task Process
```
Input: Task ID
Process: Check authorization → Delete task → Log activity
Transaction: 
  SELECT tasks (authorization check)
  → DELETE tasks
  → INSERT user_logs
```

### 5. Add Activity Process
```
Input: Activity Date, Description
Process: Insert activity → Log user activity
Transaction: INSERT activities → INSERT user_logs
```

### 6. Logout Process
```
Process: Log logout → Destroy session → Redirect
Transaction: INSERT user_logs → session_destroy()
```

## 🐛 Issues Found & Fixes

### Issue 1: User Activity Logs Incomplete
**Problem**: User activity logs showing empty activity_type in reports

**Root Cause**: The `logUserActivity()` function is not properly inserting activity types

**Current Code**:
```php
function logUserActivity($conn, $user_id, $activity_type) {
    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, activity_type) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $activity_type);
    $stmt->execute();
    $stmt->close();
}
```

**Issue**: The activity_type values being passed don't match the ENUM values in database

**Database ENUM**: `('login', 'logout')`
**Code passing**: `'create_task', 'update_task', 'delete_task', 'add_activity', 'delete_activity'`

### Fix Required:
1. **Option 1**: Update database ENUM to include all activity types:
```sql
ALTER TABLE user_logs MODIFY activity_type ENUM(
    'login', 'logout', 'create_task', 'update_task', 'delete_task', 
    'add_activity', 'delete_activity'
);
```

2. **Option 2**: Add separate activity_description field:
```sql
ALTER TABLE user_logs ADD COLUMN activity_description VARCHAR(100);
```

## 🔐 Security Features
- Session-based authentication
- Role-based access control
- SQL injection prevention (prepared statements)
- Authorization checks for data access
- Input validation

## 📈 Reporting Features
- Dashboard statistics
- Task completion rates
- User activity tracking
- Status change history
- Activity logs with timestamps

## ⚡ Performance Considerations
- Logs limited to 20 recent entries
- Indexed foreign keys
- Prepared statements for queries
- Session management

## 🚀 Demo Accounts
- **Admin**: admin1 / admin123
- **User**: user1 / password1

## 📝 Usage Instructions
1. Login dengan akun demo
2. Admin bisa melihat semua data, User hanya data sendiri
3. Gunakan tombol "Add New Task" untuk membuat tugas
4. Update status task dengan dropdown
5. Catat aktivitas harian dengan "Add New Activity"
6. Admin bisa melihat reports untuk monitoring

## 🔧 Setup Instructions
1. Import database SQL
2. Update config.php dengan credentials database
3. Pastikan PHP sessions enabled
4. Deploy ke web server dengan PHP support