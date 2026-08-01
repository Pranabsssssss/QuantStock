<?php
/**
 * QuantStock — Forecast API
 * 
 * Collects SQL data → sends to Groq AI → saves response to DB.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/ai.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Forecast.php';

initApp();
requireApiAuth();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$forecastModel = new Forecast();

if ($method === 'POST') {
    $data = getJsonInput();
    $action = $data['action'] ?? 'forecast';

    if ($action === 'forecast') {
        // Step 1: Collect SQL data
        $productModel = new Product();
        $saleModel = new Sale();
        
        $products = $productModel->getForAI();
        $salesData = $saleModel->getForAI();

        if (empty($products)) {
            jsonResponse(['success' => false, 'message' => 'No products found. Add products first.'], 400);
        }

        // Step 2: Build context
        $context = json_encode([
            'business_date'  => date('Y-m-d'),
            'total_products' => count($products),
            'products'       => $products,
            'recent_sales'   => array_slice($salesData, 0, 200),
            'sales_days'     => 90,
        ], JSON_UNESCAPED_UNICODE);

        // Step 3: Call Groq AI
        $ai = new AIClient();
        $result = $ai->chat(
            AIClient::getForecastPrompt(),
            "Here is my business data. Analyze and provide demand forecasts:\n\n" . $context,
            0.3
        );

        if (isset($result['error'])) {
            jsonResponse(['success' => false, 'message' => $result['error']], 500);
        }

        // Step 4: Save to database
        $forecastModel->savePrediction([
            'type'     => 'forecast',
            'title'    => 'AI Demand Forecast — ' . date('d M Y'),
            'summary'  => $result['forecast_summary'] ?? 'Forecast generated successfully.',
            'data'     => $result,
            'priority' => 'high',
        ]);

        // Save individual product forecasts
        if (!empty($result['products'])) {
            foreach ($result['products'] as $fp) {
                if (!empty($fp['product_id'])) {
                    $forecastModel->saveProductForecast((int)$fp['product_id'], [
                        'forecast_type'    => 'demand',
                        'period_days'      => 30,
                        'predicted_demand' => $fp['predicted_demand_30d'] ?? 0,
                        'confidence'       => $result['confidence_score'] ?? 0,
                        'details'          => $fp,
                    ]);
                }
            }
        }

        jsonResponse(['success' => true, 'message' => 'Forecast generated successfully', 'data' => $result]);
    }

    if ($action === 'dismiss') {
        $forecastModel->dismissPrediction((int)($data['id'] ?? 0));
        jsonResponse(['success' => true, 'message' => 'Prediction dismissed']);
    }
}

if ($method === 'GET') {
    $type = $_GET['type'] ?? '';
    $predictions = $forecastModel->getLatestPredictions($type);
    jsonResponse(['success' => true, 'data' => $predictions]);
}

jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
