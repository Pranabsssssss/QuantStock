<?php
/**
 * QuantStock — Main Entry Point & Router
 * 
 * Routes all authenticated pages through a single layout.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Initialize
initApp();

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Load models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/Supplier.php';
require_once __DIR__ . '/models/Sale.php';
require_once __DIR__ . '/models/Analytics.php';
require_once __DIR__ . '/models/Forecast.php';
require_once __DIR__ . '/models/Settings.php';

// Load components
require_once __DIR__ . '/includes/components/card.php';
require_once __DIR__ . '/includes/components/table.php';
require_once __DIR__ . '/includes/components/empty-state.php';
require_once __DIR__ . '/includes/components/modal.php';

// Determine current page
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'products', 'sales', 'forecast', 'optimization', 'ai-assistant', 'analytics', 'reports', 'settings'];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pageFile)) {
    $page = 'dashboard';
    $pageFile = __DIR__ . '/pages/dashboard.php';
}

$user = getCurrentUser();
$currency = getSetting('currency', '₹');
$csrfToken = generateCSRFToken();

// Page titles
$pageTitles = [
    'dashboard'    => 'Dashboard',
    'products'     => 'Products',
    'sales'        => 'Sales',
    'forecast'     => 'Forecast',
    'optimization' => 'Optimization',
    'ai-assistant' => 'AI Assistant',
    'analytics'    => 'Analytics',
    'reports'      => 'Reports',
    'settings'     => 'Settings',
];
$pageTitle = $pageTitles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — QuantStock</title>
    <meta name="description" content="QuantStock — Quantum AI-Powered Inventory Forecasting & Optimization">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        // Set theme before paint to prevent flash
        const savedTheme = localStorage.getItem('quantstock-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/includes/components/sidebar.php'; ?>
        
        <main class="main-content">
            <?php include __DIR__ . '/includes/components/header.php'; ?>
            
            <div class="page-content" id="pageContent">
                <?php include $pageFile; ?>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Global config
        window.QUANTSTOCK = {
            csrf: '<?= e($csrfToken) ?>',
            currency: '<?= e($currency) ?>',
            page: '<?= e($page) ?>',
            userId: <?= (int)($user['id'] ?? 0) ?>,
        };
    </script>
    <script src="assets/js/app.js"></script>
    <?php if (file_exists(__DIR__ . "/assets/js/{$page}.js")): ?>
        <script src="assets/js/<?= e($page) ?>.js"></script>
    <?php endif; ?>
    <?php 
    // Also load chart-specific page JS using mapped names
    $jsMap = ['ai-assistant' => 'ai-chat'];
    $jsFile = $jsMap[$page] ?? $page;
    if ($jsFile !== $page && file_exists(__DIR__ . "/assets/js/{$jsFile}.js")):
    ?>
        <script src="assets/js/<?= e($jsFile) ?>.js"></script>
    <?php endif; ?>
    <script>lucide.createIcons();</script>
</body>
</html>
