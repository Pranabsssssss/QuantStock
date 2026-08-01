<?php
/**
 * QuantStock — Dashboard API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Analytics.php';

initApp();
requireApiAuth();
header('Content-Type: application/json');

$analytics = new Analytics();
$saleModel = new Sale();

$action = $_GET['action'] ?? 'summary';

switch ($action) {
    case 'summary':
        jsonResponse(['success' => true, 'data' => $analytics->getDashboardSummary()]);
        break;

    case 'sales_trend':
        $days = (int)($_GET['days'] ?? 30);
        jsonResponse(['success' => true, 'data' => $saleModel->getSalesTrend($days)]);
        break;

    case 'recent_activity':
        jsonResponse(['success' => true, 'data' => $analytics->getRecentActivity(10)]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
}
