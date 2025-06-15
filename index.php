<?php
require_once 'config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #3498db;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .skeleton {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
      background-color: #e5e7eb;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: .5;
      }
    }

    .nav-btn.active {
      background-color: #1e40af !important;
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen">
  <!-- Navigation -->
  <nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <i class="fas fa-tasks text-blue-600 text-xl mr-2"></i>
          <h1 class="text-lg sm:text-xl font-bold text-gray-800">Task Management</h1>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-2 lg:space-x-4">
          <span class="text-gray-600 text-sm lg:text-base">
            <i class="fas fa-user mr-1"></i>
            <?php echo htmlspecialchars($_SESSION['username']); ?>
          </span>
          <button onclick="showSection('dashboard')" class="nav-btn bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600 transition-colors">
            <i class="fas fa-chart-line mr-1"></i>
            <span class="hidden lg:inline">Dashboard</span>
          </button>
          <button onclick="showSection('tasks')" class="nav-btn bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600 transition-colors">
            <i class="fas fa-tasks mr-1"></i>
            <span class="hidden lg:inline">Tasks</span>
          </button>
          <button onclick="showSection('activities')" class="nav-btn bg-purple-500 text-white px-3 py-2 rounded text-sm hover:bg-purple-600 transition-colors">
            <i class="fas fa-clipboard-list mr-1"></i>
            <span class="hidden lg:inline">Activities</span>
          </button>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <button onclick="showSection('reports')" class="nav-btn bg-orange-500 text-white px-3 py-2 rounded text-sm hover:bg-orange-600 transition-colors">
              <i class="fas fa-chart-bar mr-1"></i>
              <span class="hidden lg:inline">Reports</span>
            </button>
          <?php endif; ?>
          <a href="logout.php" class="bg-red-500 text-white px-3 py-2 rounded text-sm hover:bg-red-600 transition-colors">
            <i class="fas fa-sign-out-alt mr-1"></i>
            <span class="hidden lg:inline">Logout</span>
          </a>
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden">
          <button id="mobile-menu-btn" class="text-gray-600 hover:text-gray-900 focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Mobile Navigation Menu -->
      <div id="mobile-menu" class="hidden md:hidden pb-4">
        <div class="flex flex-col space-y-2">
          <div class="text-center py-2 border-b border-gray-200">
            <span class="text-gray-600 text-sm">
              <i class="fas fa-user mr-1"></i>
              <?php echo htmlspecialchars($_SESSION['username']); ?>
            </span>
          </div>
          <button onclick="showSection('dashboard')" class="nav-btn bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600 transition-colors">
            <i class="fas fa-chart-line mr-2"></i>Dashboard
          </button>
          <button onclick="showSection('tasks')" class="nav-btn bg-green-500 text-white px-4 py-2 rounded text-sm hover:bg-green-600 transition-colors">
            <i class="fas fa-tasks mr-2"></i>Tasks
          </button>
          <button onclick="showSection('activities')" class="nav-btn bg-purple-500 text-white px-4 py-2 rounded text-sm hover:bg-purple-600 transition-colors">
            <i class="fas fa-clipboard-list mr-2"></i>Activities
          </button>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <button onclick="showSection('reports')" class="nav-btn bg-orange-500 text-white px-4 py-2 rounded text-sm hover:bg-orange-600 transition-colors">
              <i class="fas fa-chart-bar mr-2"></i>Reports
            </button>
          <?php endif; ?>
          <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded text-sm hover:bg-red-600 transition-colors text-center">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
          </a>
        </div>
      </div>
    </div>
  </nav>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
    <!-- Dashboard Section -->
    <div id="dashboard-section" class="section">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <i class="fas fa-tasks text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-sm sm:text-lg font-semibold text-gray-700">Total Tasks</h3>
              <p id="totalTasks" class="text-2xl sm:text-3xl font-bold text-blue-600">
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-sm sm:text-lg font-semibold text-gray-700">Completed</h3>
              <p id="completedTasks" class="text-2xl sm:text-3xl font-bold text-green-600">
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200 sm:col-span-2 lg:col-span-1">
          <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
              <i class="fas fa-clock text-orange-600 text-xl"></i>
            </div>
            <div class="ml-4">
              <h3 class="text-sm sm:text-lg font-semibold text-gray-700">Pending</h3>
              <p id="pendingTasks" class="text-2xl sm:text-3xl font-bold text-orange-600">
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
          <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
              <i class="fas fa-chart-pie mr-2 text-gray-600"></i>
              Task Distribution
            </h3>
            <div class="relative h-64">
              <canvas id="taskChart"></canvas>
              <div id="chartLoading" class="absolute inset-0 flex items-center justify-center">
                <div class="loading"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="lg:col-span-2">
          <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
              <i class="fas fa-info-circle mr-2 text-gray-600"></i>
              Quick Overview
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-600 font-medium">Today's Tasks</p>
                <p id="todayTasks" class="text-2xl font-bold text-blue-800">
                </p>
              </div>
              <div class="p-4 bg-red-50 rounded-lg">
                <p class="text-sm text-red-600 font-medium">Overdue</p>
                <p id="overdueTasks" class="text-2xl font-bold text-red-800">
                </p>
              </div>
              <div class="p-4 bg-yellow-50 rounded-lg">
                <p class="text-sm text-yellow-600 font-medium">In Progress</p>
                <p id="inProgressTasks" class="text-2xl font-bold text-yellow-800">
                </p>
              </div>
              <div class="p-4 bg-purple-50 rounded-lg">
                <p class="text-sm text-purple-600 font-medium">This Week</p>
                <p id="weekTasks" class="text-2xl font-bold text-purple-800">
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tasks Section -->
    <div id="tasks-section" class="section hidden">
      <!-- Add Task Form -->
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-plus-circle mr-2 text-green-600"></i>
          Add New Task
        </h2>
        <form id="taskForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-user mr-1"></i>Assign to User
            </label>
            <select id="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="">
                <div class="loading"></div>
                Loading users...
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-calendar mr-1"></i>Due Date
            </label>
            <input type="date" id="due_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-flag mr-1"></i>Status
            </label>
            <select id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <option value="pending">📋 Pending</option>
              <option value="in_progress">⚡ In Progress</option>
              <option value="completed">✅ Completed</option>
            </select>
          </div>

          <div class="sm:col-span-2 lg:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-heading mr-1"></i>Task Title
            </label>
            <input type="text" id="title" placeholder="Enter task title..." required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>

          <div class="sm:col-span-2 lg:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-align-left mr-1"></i>Description
            </label>
            <textarea id="description" placeholder="Enter task description..." rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
          </div>

          <div class="sm:col-span-2 lg:col-span-3">
            <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <i class="fas fa-plus mr-2"></i>Add Task
            </button>
          </div>
        </form>
      </div>

      <!-- Tasks List -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-list mr-2 text-blue-600"></i>
            Task List
          </h2>
        </div>

        <div class="overflow-x-auto">
          <div id="tasksLoading" class="p-8 text-center">
            <div class="loading mx-auto mb-4"></div>
            <p class="text-gray-500">Loading tasks...</p>
          </div>

          <table id="tasksTableContainer" class="min-w-full table-auto hidden">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Assigned To</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody id="tasksTable" class="bg-white divide-y divide-gray-200">
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Activities Section -->
    <div id="activities-section" class="section hidden">
      <!-- Add Activity Form -->
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-plus-circle mr-2 text-purple-600"></i>
          Add Daily Activity
        </h2>
        <form id="activityForm" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-day mr-1"></i>Activity Date
              </label>
              <input type="date" id="activity_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-edit mr-1"></i>Activity Description
            </label>
            <textarea id="activity_desc" placeholder="Describe your activity..." rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"></textarea>
          </div>

          <button type="submit" class="w-full sm:w-auto bg-purple-600 text-white py-2 px-6 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Activity
          </button>
        </form>
      </div>

      <!-- Activities List -->
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-history mr-2 text-purple-600"></i>
          My Activities
        </h2>

        <div id="activitiesLoading" class="text-center py-8">
          <div class="loading mx-auto mb-4"></div>
          <p class="text-gray-500">Loading activities...</p>
        </div>

        <div id="activitiesList" class="space-y-4 hidden">
        </div>
      </div>
    </div>

    <!-- Reports Section (Admin Only) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <div id="reports-section" class="section hidden">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
          <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
              <i class="fas fa-exchange-alt mr-2 text-orange-600"></i>
              Task Status Logs
            </h3>
            <div id="taskLogsLoading" class="text-center py-8">
              <div class="loading mx-auto mb-4"></div>
              <p class="text-gray-500">Loading task logs...</p>
            </div>
            <div id="taskLogsTable" class="overflow-x-auto hidden">
            </div>
          </div>

          <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
              <i class="fas fa-user-clock mr-2 text-orange-600"></i>
              User Activity Logs
            </h3>
            <div id="userLogsLoading" class="text-center py-8">
              <div class="loading mx-auto mb-4"></div>
              <p class="text-gray-500">Loading user logs...</p>
            </div>
            <div id="userLogsTable" class="overflow-x-auto hidden">
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
    let taskChart;

    // Mobile menu toggle
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('hidden');
    });

    // Load data saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
      loadUsers();
      loadTasks();
      loadActivities();

      // Set tanggal hari ini sebagai default
      document.getElementById('activity_date').value = new Date().toISOString().split('T')[0];
      document.getElementById('due_date').value = new Date().toISOString().split('T')[0];

      // Set dashboard as active by default
      showSection('dashboard');
    });

    // Navigation functions
    function showSection(section) {
      // Hide all sections
      document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
      document.getElementById(section + '-section').classList.remove('hidden');

      // Update active nav button
      document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');

      // Hide mobile menu
      document.getElementById('mobile-menu').classList.add('hidden');

      if (section === 'reports') {
        loadReports();
      }
    }

    // Load users for dropdown
    function loadUsers() {
      fetch('api.php?action=get_users')
        .then(response => response.json())
        .then(users => {
          const select = document.getElementById('user_id');
          select.innerHTML = '<option value="">Select User</option>';
          users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.user_id;
            option.textContent = user.username;
            select.appendChild(option);
          });
        })
        .catch(error => {
          const select = document.getElementById('user_id');
          select.innerHTML = '<option value="">Error loading users</option>';
        });
    }

    // Load tasks
    function loadTasks() {
      fetch('api.php?action=get_tasks')
        .then(response => response.json())
        .then(tasks => {
          const tbody = document.getElementById('tasksTable');
          const loading = document.getElementById('tasksLoading');
          const container = document.getElementById('tasksTableContainer');

          tbody.innerHTML = '';
          loading.classList.add('hidden');
          container.classList.remove('hidden');

          updateDashboard(tasks);

          if (tasks.length === 0) {
            tbody.innerHTML = `
              <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                  <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                  <p>No tasks found</p>
                </td>
              </tr>
            `;
            return;
          }

          tasks.forEach(task => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            const statusColors = {
              'pending': 'bg-yellow-100 text-yellow-800',
              'in_progress': 'bg-blue-100 text-blue-800',
              'completed': 'bg-green-100 text-green-800'
            };

            const statusIcons = {
              'pending': 'fas fa-clock',
              'in_progress': 'fas fa-spinner',
              'completed': 'fas fa-check-circle'
            };

            tr.innerHTML = `
              <td class="px-4 py-3 text-sm font-medium text-gray-900">#${task.task_id}</td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">${task.title}</div>
                <div class="text-sm text-gray-500 truncate max-w-xs">${task.description || ''}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 hidden sm:table-cell">
                <i class="fas fa-user mr-1 text-gray-400"></i>${task.username}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">
                <i class="fas fa-calendar mr-1 text-gray-400"></i>${task.due_date}
              </td>
              <td class="px-4 py-3">
                <select class="status-select text-xs px-2 py-1 rounded-full border-0 ${statusColors[task.status]}" data-task-id="${task.task_id}">
                  <option value="pending" ${task.status === 'pending' ? 'selected' : ''}>📋 Pending</option>
                  <option value="in_progress" ${task.status === 'in_progress' ? 'selected' : ''}>⚡ In Progress</option>
                  <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>✅ Completed</option>
                </select>
              </td>
              <td class="px-4 py-3">
                <button class="delete-btn text-red-600 hover:text-red-800 p-1" data-task-id="${task.task_id}" title="Delete task">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            `;
            tbody.appendChild(tr);
          });

          // Add event listeners
          document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
              updateTaskStatus(this.dataset.taskId, this.value);
            });
          });

          document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
              deleteTask(this.dataset.taskId);
            });
          });
        })
        .catch(error => {
          const loading = document.getElementById('tasksLoading');
          loading.innerHTML = `
            <div class="text-center text-red-500">
              <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
              <p>Error loading tasks</p>
            </div>
          `;
        });
    }

    // Update dashboard statistics
    function updateDashboard(tasks) {
      const statusCounts = {
        total: tasks.length,
        pending: tasks.filter(t => t.status === 'pending').length,
        in_progress: tasks.filter(t => t.status === 'in_progress').length,
        completed: tasks.filter(t => t.status === 'completed').length
      };

      // Calculate additional stats
      const today = new Date().toISOString().split('T')[0];
      const todayTasks = tasks.filter(t => t.due_date === today).length;
      const overdueTasks = tasks.filter(t => new Date(t.due_date) < new Date() && t.status !== 'completed').length;

      const weekStart = new Date();
      weekStart.setDate(weekStart.getDate() - weekStart.getDay());
      const weekEnd = new Date(weekStart);
      weekEnd.setDate(weekStart.getDate() + 6);
      const weekTasks = tasks.filter(t => {
        const taskDate = new Date(t.due_date);
        return taskDate >= weekStart && taskDate <= weekEnd;
      }).length;

      document.getElementById('totalTasks').textContent = statusCounts.total;
      document.getElementById('completedTasks').textContent = statusCounts.completed;
      document.getElementById('pendingTasks').textContent = statusCounts.pending;
      document.getElementById('todayTasks').textContent = todayTasks;
      document.getElementById('overdueTasks').textContent = overdueTasks;
      document.getElementById('inProgressTasks').textContent = statusCounts.in_progress;
      document.getElementById('weekTasks').textContent = weekTasks;

      updateChart(statusCounts);
    }

    // Update chart
    function updateChart(statusCounts) {
      const ctx = document.getElementById('taskChart').getContext('2d');
      const chartLoading = document.getElementById('chartLoading');

      if (taskChart) {
        taskChart.destroy();
      }

      chartLoading.style.display = 'none';

      taskChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Pending', 'In Progress', 'Completed'],
          datasets: [{
            data: [statusCounts.pending, statusCounts.in_progress, statusCounts.completed],
            backgroundColor: ['#f59e0b', '#3b82f6', '#10b981'],
            borderWidth: 2,
            borderColor: '#ffffff',
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 20,
                usePointStyle: true,
                font: {
                  size: 12
                }
              }
            }
          },
          cutout: '60%'
        }
      });
    }

    // Add new task
    document.getElementById('taskForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<div class="loading mr-2"></div>Adding...';
      submitBtn.disabled = true;

      const taskData = {
        user_id: document.getElementById('user_id').value,
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        due_date: document.getElementById('due_date').value,
        status: document.getElementById('status').value
      };

      fetch('api.php?action=add_task', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(taskData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4';
            successDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Task added successfully!';
            this.parentNode.insertBefore(successDiv, this);

            setTimeout(() => successDiv.remove(), 3000);

            this.reset();
            document.getElementById('due_date').value = new Date().toISOString().split('T')[0];
            loadTasks();
          } else {
            throw new Error(data.error || 'Failed to add task');
          }
        })
        .catch(error => {
          const errorDiv = document.createElement('div');
          errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
          errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Error: ' + error.message;
          this.parentNode.insertBefore(errorDiv, this);

          setTimeout(() => errorDiv.remove(), 5000);
        })
        .finally(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
    });

    // Update task status
    function updateTaskStatus(taskId, newStatus) {
      fetch('api.php?action=update_task_status', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            task_id: taskId,
            status: newStatus
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            loadTasks();
          }
        })
        .catch(error => {
          console.error('Error updating task status:', error);
          loadTasks(); // Reload to revert changes
        });
    }

    // Delete task
    function deleteTask(taskId) {
      if (confirm('Are you sure you want to delete this task?')) {
        fetch(`api.php?action=delete_task&task_id=${taskId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              loadTasks();
            }
          })
          .catch(error => {
            console.error('Error deleting task:', error);
          });
      }
    }

    // Load activities
    function loadActivities() {
      fetch('api.php?action=get_activities')
        .then(response => response.json())
        .then(activities => {
          const container = document.getElementById('activitiesList');
          const loading = document.getElementById('activitiesLoading');

          loading.classList.add('hidden');
          container.classList.remove('hidden');
          container.innerHTML = '';

          if (activities.length === 0) {
            container.innerHTML = `
              <div class="text-center py-8 text-gray-500">
                <i class="fas fa-clipboard-list text-4xl mb-2 text-gray-300"></i>
                <p>No activities recorded yet</p>
              </div>
            `;
            return;
          }

          activities.forEach(activity => {
            const div = document.createElement('div');
            div.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow';
            div.innerHTML = `
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center mb-2">
                    <i class="fas fa-calendar-day text-purple-600 mr-2"></i>
                    <p class="font-medium text-gray-800">${activity.activity_date}</p>
                  </div>
                  <p class="text-gray-600 leading-relaxed">${activity.activity_desc}</p>
                </div>
                <button class="delete-activity-btn text-red-500 hover:text-red-700 ml-4 p-1" data-activity-id="${activity.activity_id}" title="Delete activity">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            `;
            container.appendChild(div);
          });

          // Add delete event listeners
          document.querySelectorAll('.delete-activity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
              deleteActivity(this.dataset.activityId);
            });
          });
        })
        .catch(error => {
          const loading = document.getElementById('activitiesLoading');
          loading.innerHTML = `
            <div class="text-center text-red-500">
              <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
              <p>Error loading activities</p>
            </div>
          `;
        });
    }

    // Add activity
    document.getElementById('activityForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<div class="loading mr-2"></div>Adding...';
      submitBtn.disabled = true;

      const activityData = {
        activity_date: document.getElementById('activity_date').value,
        activity_desc: document.getElementById('activity_desc').value
      };

      fetch('api.php?action=add_activity', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(activityData)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const successDiv = document.createElement('div');
            successDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4';
            successDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Activity added successfully!';
            this.parentNode.insertBefore(successDiv, this);

            setTimeout(() => successDiv.remove(), 3000);

            document.getElementById('activity_desc').value = '';
            loadActivities();
          } else {
            throw new Error(data.error || 'Failed to add activity');
          }
        })
        .catch(error => {
          const errorDiv = document.createElement('div');
          errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
          errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Error: ' + error.message;
          this.parentNode.insertBefore(errorDiv, this);

          setTimeout(() => errorDiv.remove(), 5000);
        })
        .finally(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
    });

    // Delete activity
    function deleteActivity(activityId) {
      if (confirm('Are you sure you want to delete this activity?')) {
        fetch(`api.php?action=delete_activity&activity_id=${activityId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              loadActivities();
            }
          })
          .catch(error => {
            console.error('Error deleting activity:', error);
          });
      }
    }

    // Load reports (Admin only)
    function loadReports() {
      // Load task status logs
      fetch('api.php?action=get_task_logs')
        .then(response => response.json())
        .then(logs => {
          const container = document.getElementById('taskLogsTable');
          const loading = document.getElementById('taskLogsLoading');

          loading.classList.add('hidden');
          container.classList.remove('hidden');

          if (logs.length === 0) {
            container.innerHTML = `
              <div class="text-center py-8 text-gray-500">
                <i class="fas fa-file-alt text-4xl mb-2 text-gray-300"></i>
                <p>No task logs available</p>
              </div>
            `;
            return;
          }

          let html = `
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left font-medium text-gray-700">Task</th>
                  <th class="px-3 py-2 text-left font-medium text-gray-700">From</th>
                  <th class="px-3 py-2 text-left font-medium text-gray-700">To</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
          `;

          logs.forEach(log => {
            html += `
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 text-gray-900">${log.title}</td>
                <td class="px-3 py-2">
                  <span class="inline-flex px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                    ${log.old_status}
                  </span>
                </td>
                <td class="px-3 py-2">
                  <span class="inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                    ${log.new_status}
                  </span>
                </td>
              </tr>
            `;
          });

          html += '</tbody></table>';
          container.innerHTML = html;
        })
        .catch(error => {
          const loading = document.getElementById('taskLogsLoading');
          loading.innerHTML = `
            <div class="text-center text-red-500">
              <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
              <p>Error loading task logs</p>
            </div>
          `;
        });

      // Load user activity logs
      fetch('api.php?action=get_user_logs')
        .then(response => response.json())
        .then(logs => {
          const container = document.getElementById('userLogsTable');
          const loading = document.getElementById('userLogsLoading');

          loading.classList.add('hidden');
          container.classList.remove('hidden');

          if (logs.length === 0) {
            container.innerHTML = `
              <div class="text-center py-8 text-gray-500">
                <i class="fas fa-users text-4xl mb-2 text-gray-300"></i>
                <p>No user logs available</p>
              </div>
            `;
            return;
          }

          let html = `
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left font-medium text-gray-700">User</th>
                  <th class="px-3 py-2 text-left font-medium text-gray-700">Activity</th>
                  <th class="px-3 py-2 text-left font-medium text-gray-700">Time</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
          `;

          logs.forEach(log => {
            html += `
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2">
                  <div class="flex items-center">
                    <i class="fas fa-user text-gray-400 mr-2"></i>
                    <span class="text-gray-900">${log.username}</span>
                  </div>
                </td>
                <td class="px-3 py-2 text-gray-600">${log.activity_type}</td>
                <td class="px-3 py-2 text-gray-500 text-xs">${log.activity_time}</td>
              </tr>
            `;
          });

          html += '</tbody></table>';
          container.innerHTML = html;
        })
        .catch(error => {
          const loading = document.getElementById('userLogsLoading');
          loading.innerHTML = `
            <div class="text-center text-red-500">
              <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
              <p>Error loading user logs</p>
            </div>
          `;
        });
    }
  </script>
</body>

</html>