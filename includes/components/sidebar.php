<?php
/**
 * QuantStock — Sidebar Component
 * 
 * Collapsible sidebar with smooth animations.
 * Usage: include this file within the main layout.
 */

$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
if ($currentPage === 'index') {
    // Determine from query param
    $currentPage = $_GET['page'] ?? 'dashboard';
}

$menuItems = [
    ['id' => 'dashboard',    'label' => 'Dashboard',    'icon' => 'layout-dashboard', 'page' => 'dashboard'],
    ['id' => 'products',     'label' => 'Products',     'icon' => 'package',          'page' => 'products'],
    ['id' => 'sales',        'label' => 'Sales',        'icon' => 'shopping-cart',     'page' => 'sales'],
    ['id' => 'forecast',     'label' => 'Forecast',     'icon' => 'trending-up',      'page' => 'forecast',     'badge' => 'Soon'],
    ['id' => 'optimization', 'label' => 'Optimization', 'icon' => 'settings-2',       'page' => 'optimization', 'badge' => 'Soon'],
    ['id' => 'ai-assistant', 'label' => 'AI Assistant',  'icon' => 'bot',             'page' => 'ai-assistant', 'badge' => 'Soon'],
    ['id' => 'analytics',    'label' => 'Analytics',    'icon' => 'bar-chart-3',      'page' => 'analytics'],
    ['id' => 'reports',      'label' => 'Reports',      'icon' => 'file-text',        'page' => 'reports'],
    ['id' => 'settings',     'label' => 'Settings',     'icon' => 'settings',         'page' => 'settings'],
];

$user = getCurrentUser();
?>

<aside class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <i data-lucide="brain-circuit"></i>
            </div>
            <div class="logo-text">
                <h1>QuantStock</h1>
                <span>Quantum AI-Powered Inventory Intelligence</span>
            </div>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i data-lucide="panel-left-close"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php foreach ($menuItems as $item): ?>
                <li class="nav-item <?= $currentPage === $item['page'] ? 'active' : '' ?>">
                    <a href="?page=<?= $item['page'] ?>" class="nav-link" data-page="<?= $item['page'] ?>">
                        <i data-lucide="<?= $item['icon'] ?>"></i>
                        <span class="nav-label"><?= $item['label'] ?></span>
                        <?php if (!empty($item['badge'])): ?>
                            <span class="nav-badge"><?= $item['badge'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- User Section -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar">
                <?php if ($user && $user['avatar']): ?>
                    <img src="<?= e($user['avatar']) ?>" alt="<?= e($user['name']) ?>">
                <?php else: ?>
                    <span><?= $user ? strtoupper(substr($user['name'], 0, 1)) : 'A' ?></span>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <p class="user-name"><?= e($user['name'] ?? 'Admin') ?></p>
                <p class="user-role"><?= e(ucfirst($user['role'] ?? 'admin')) ?></p>
            </div>
        </div>
        <a href="logout.php" class="nav-link logout-link">
            <i data-lucide="log-out"></i>
            <span class="nav-label">Logout</span>
        </a>
    </div>
</aside>

<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
