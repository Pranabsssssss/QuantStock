<?php
/**
 * QuantStock — Analytics API
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
$action = $_GET['action'] ?? 'overview';
$days = (int)($_GET['days'] ?? 30);

switch ($action) {
    case 'overview':
        jsonResponse(['success' => true, 'data' => [
            'total_revenue' => $analytics->getTotalRevenue(),
            'total_profit'  => $analytics->getTotalProfit(),
            'health_score'  => $analytics->getInventoryHealthScore(),
        ]]);
        break;
    case 'revenue_trend':
        jsonResponse(['success' => true, 'data' => $analytics->getRevenueTrend(6)]);
        break;
    case 'profit_trend':
        jsonResponse(['success' => true, 'data' => $analytics->getProfitTrend(6)]);
        break;
    case 'top_products':
        jsonResponse(['success' => true, 'data' => $analytics->getTopProducts($days)]);
        break;
    case 'slow_moving':
        jsonResponse(['success' => true, 'data' => $analytics->getSlowMovingProducts($days)]);
        break;
    case 'dead_stock':
        jsonResponse(['success' => true, 'data' => $analytics->getDeadStock()]);
        break;
    case 'category_performance':
        jsonResponse(['success' => true, 'data' => $analytics->getCategoryPerformance($days)]);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action'], 400);
}
