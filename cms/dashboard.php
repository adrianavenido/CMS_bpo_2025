<?php
/**
 * Protected Dashboard Page
 * Requires authentication and displays user-specific content
 */

require_once __DIR__ . '/core/Middleware.php';

// Require authentication
requireAuth();

// Get current user
$middleware = new Middleware();
$currentUser = $middleware->getCurrentUser();

// Log page access
$middleware->logActivity('page_access', 'Accessed dashboard');

// Get session for flash messages
$session = Session::getInstance();
$flashMessage = $session->flash('success') ?? $session->flash('error');
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BPO CMS - Dashboard</title>

  <!-- Smart default theme logic -->
  <script>
    const savedTheme = localStorage.getItem('theme');
    if (!savedTheme) {
      const hour = new Date().getHours();
      const isNight = hour >= 18 || hour < 6;
      document.documentElement.classList.toggle('dark', isNight);
    } else {
      document.documentElement.classList.toggle('dark', savedTheme === 'dark');
    }
  </script>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            'bpo-primary': '#0d47a1',
            'bpo-accent': '#1e88e5',
            'dark-bg': '#0f172a',
            'dark-surface': '#1e293b',
            'dark-border': '#334155',
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 dark:bg-dark-bg text-gray-800 dark:text-white transition-colors duration-300">
  
  <!-- Flash Message -->
  <?php if ($flashMessage): ?>
  <div id="flashMessage" class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 
    <?php echo strpos($flashMessage, 'success') !== false ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
    <div class="flex items-center justify-between">
      <span><?php echo htmlspecialchars($flashMessage); ?></span>
      <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
  <script>
    setTimeout(() => {
      const flash = document.getElementById('flashMessage');
      if (flash) flash.remove();
    }, 5000);
  </script>
  <?php endif; ?>

  <!-- Header -->
  <header class="bg-white dark:bg-dark-surface shadow-lg border-b dark:border-dark-border">
    <div class="flex items-center justify-between px-6 py-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-headset text-bpo-primary dark:text-bpo-accent text-2xl"></i>
        <h1 class="text-xl font-bold text-bpo-primary dark:text-white">BPO CMS</h1>
      </div>
      <div class="flex items-center space-x-4">
        <button id="themeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-dark-surface transition">
          <i id="themeIcon" class="fas fa-sun text-xl dark:text-white"></i>
        </button>
        <div class="relative">
          <button class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-dark-surface transition">
            <i class="fas fa-bell text-xl"></i>
            <span class="absolute -top-1 -right-1 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
          </button>
        </div>
        <div class="flex items-center space-x-2">
          <img src="/placeholder.svg?height=32&width=32" alt="User" class="w-8 h-8 rounded-full" />
          <div class="text-sm">
            <div class="font-medium"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
            <div class="text-gray-500 dark:text-gray-400 text-xs"><?php echo htmlspecialchars(ucfirst($currentUser['role'])); ?></div>
          </div>
          <div class="relative">
            <button id="userMenuBtn" class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-dark-surface transition">
              <i class="fas fa-chevron-down text-sm"></i>
            </button>
            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-dark-surface rounded-lg shadow-lg border dark:border-dark-border">
              <a href="/settings.html" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-dark-bg">Settings</a>
              <a href="/cms/auth/logout.php" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-dark-bg text-red-600">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-dark-surface shadow-lg border-r dark:border-dark-border">
      <nav class="mt-6">
        <div class="px-6 py-2">
          <h2 class="text-sm uppercase font-semibold text-gray-500 dark:text-gray-400 tracking-wider">Navigation</h2>
        </div>
        <ul class="space-y-2 px-4 mt-4">
          <li>
            <a href="/index.html" class="flex items-center px-4 py-3 bg-bpo-primary text-white rounded-lg">
              <i class="fas fa-chart-line mr-3"></i>Dashboard
            </a>
          </li>
          <li>
            <a href="/agents.html" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-bg rounded-lg">
              <i class="fas fa-users mr-3"></i>Agents
            </a>
          </li>
          <li>
            <a href="/clients.html" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-bg rounded-lg">
              <i class="fas fa-building mr-3"></i>Clients
            </a>
          </li>
          <li>
            <a href="/support-tickets.html" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-bg rounded-lg">
              <i class="fas fa-ticket-alt mr-3"></i>Support Tickets
            </a>
          </li>
          <li>
            <a href="/task-management.html" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-bg rounded-lg">
              <i class="fas fa-tasks mr-3"></i>Task Management
            </a>
          </li>
          <?php if (in_array($currentUser['role'], ['admin', 'hr'])): ?>
          <li>
            <a href="/hr-active-cases.html" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-bg rounded-lg">
              <i class="fas fa-user-tie mr-3"></i>HR Cases
            </a>
          </li>
          <?php endif; ?>
          <li>
            <a href="/reports.html" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-bg rounded-lg">
              <i class="fas fa-chart-bar mr-3"></i>Reports
            </a>
          </li>
          <?php if ($currentUser['role'] === 'admin'): ?>
          <li>
            <a href="/settings.html" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-bg rounded-lg">
              <i class="fas fa-cog mr-3"></i>Settings
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
      <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</h2>
        <p class="text-gray-600 dark:text-gray-400">Here's what's happening today.</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-dark-surface rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
              <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Agents</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">24</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-dark-surface rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
              <i class="fas fa-ticket-alt text-green-600 dark:text-green-400 text-xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Open Tickets</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">12</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-dark-surface rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
              <i class="fas fa-tasks text-yellow-600 dark:text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Tasks</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">8</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-dark-surface rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
              <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Urgent Cases</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">3</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white dark:bg-dark-surface rounded-lg shadow">
        <div class="px-6 py-4 border-b dark:border-dark-border">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="flex items-center space-x-3">
              <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
              <div class="flex-1">
                <p class="text-sm text-gray-900 dark:text-white">New support ticket created</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">2 minutes ago</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-2 h-2 bg-green-500 rounded-full"></div>
              <div class="flex-1">
                <p class="text-sm text-gray-900 dark:text-white">Task completed by Mike Johnson</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">15 minutes ago</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
              <div class="flex-1">
                <p class="text-sm text-gray-900 dark:text-white">New HR case assigned</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">1 hour ago</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Theme toggle and user menu scripts -->
  <script>
    const toggleButton = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const html = document.documentElement;
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenu = document.getElementById('userMenu');

    const updateIcon = () => {
      icon.classList.toggle('fa-sun', !html.classList.contains('dark'));
      icon.classList.toggle('fa-moon', html.classList.contains('dark'));
    };

    toggleButton.addEventListener('click', () => {
      html.classList.toggle('dark');
      localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
      updateIcon();
    });

    userMenuBtn.addEventListener('click', () => {
      userMenu.classList.toggle('hidden');
    });

    // Close user menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.add('hidden');
      }
    });

    updateIcon();
  </script>

</body>
</html> 