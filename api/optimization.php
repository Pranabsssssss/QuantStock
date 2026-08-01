<?php
/**
 * QuantStock — Optimization API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/ai.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/Forecast.php';

initApp();
requireApiAuth();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$forecastModel = new Forecast();

if ($method === 'POST') {
    $data = getJsonInput();

    $productModel = new Product();
    $saleModel = new Sale();
    $analyticsModel = new Analytics();

    $products = $productModel->getForAI();
    $salesData = $saleModel->getForAI();

    if (empty($products)) {
        jsonResponse(['success' => false, 'message' => 'No products found. Add products first.'], 400);
    }

    $context = json_encode([
        'business_date'       => date('Y-m-d'),
        'total_products'      => count($products),
        'inventory_health'    => $analyticsModel->getInventoryHealthScore(),
        'total_inventory_value' => $productModel->getTotalValue(),
        'products'            => $products,
        'recent_sales'        => array_slice($salesData, 0, 200),
    ], JSON_UNESCAPED_UNICODE);

    $ai = new AIClient();
    $result = $ai->chat(
        AIClient::getOptimizationPrompt(),
        "Analyze my inventory and provide optimization recommendations:\n\n" . $context,
        0.3
    );

    if (isset($result['error'])) {
        jsonResponse(['success' => false, 'message' => $result['error']], 500);
    }

    $forecastModel->savePrediction([
        'type'     => 'optimization',
        'title'    => 'AI Inventory Optimization — ' . date('d M Y'),
        'summary'  => $result['optimization_summary'] ?? 'Optimization analysis complete.',
        'data'     => $result,
        'priority' => 'high',
    ]);

    jsonResponse(['success' => true, 'message' => 'Optimization analysis complete', 'data' => $result]);
}

if ($method === 'GET') {
    $predictions = $forecastModel->getLatestPredictions('optimization');
    jsonResponse(['success' => true, 'data' => $predictions]);
}

jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
