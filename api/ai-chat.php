<?php
/**
 * QuantStock — AI Chat API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/ai.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Analytics.php';

initApp();
requireApiAuth();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = Database::getInstance();
$userId = $_SESSION['user_id'];

if ($method === 'POST') {
    $data = getJsonInput();
    $action = $data['action'] ?? 'chat';

    if ($action === 'chat') {
        $message = trim($data['message'] ?? '');
        if (empty($message)) {
            jsonResponse(['success' => false, 'message' => 'Message cannot be empty'], 400);
        }

        // Save user message
        $stmt = $pdo->prepare("INSERT INTO ai_chat_history (user_id, role, message) VALUES (?, 'user', ?)");
        $stmt->execute([$userId, $message]);

        // Collect business context from SQL
        $productModel = new Product();
        $saleModel = new Sale();
        $analyticsModel = new Analytics();

        $context = [
            'business_date'     => date('Y-m-d'),
            'total_products'    => $productModel->getCount(),
            'inventory_value'   => $productModel->getTotalValue(),
            'today_revenue'     => $saleModel->getTodayRevenue(),
            'month_revenue'     => $saleModel->getMonthRevenue(),
            'total_revenue'     => $analyticsModel->getTotalRevenue(),
            'total_profit'      => $analyticsModel->getTotalProfit(),
            'low_stock_count'   => $productModel->getLowStockCount(),
            'inventory_health'  => $analyticsModel->getInventoryHealthScore(),
            'top_products'      => $productModel->getTopSelling(30, 5),
            'low_stock_items'   => $productModel->getLowStock(5),
        ];

        // Get recent chat history for context
        $historyStmt = $pdo->prepare("SELECT role, message FROM ai_chat_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        $historyStmt->execute([$userId]);
        $history = array_reverse($historyStmt->fetchAll());

        // Build the user message with context
        $contextMessage = "Business Context (real data from database):\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\nUser Question: " . $message;

        // Update last message to include context
        $messagesForAI = [];
        foreach ($history as $h) {
            if ($h['role'] === 'user' || $h['role'] === 'assistant') {
                $content = $h['message'];
                // For assistant messages, try to extract just the response text
                if ($h['role'] === 'assistant') {
                    $parsed = json_decode($content, true);
                    $content = $parsed['response'] ?? $content;
                }
                $messagesForAI[] = ['role' => $h['role'], 'content' => $content];
            }
        }
        // Replace last user message with contextual version
        if (!empty($messagesForAI)) {
            $messagesForAI[count($messagesForAI) - 1] = ['role' => 'user', 'content' => $contextMessage];
        }

        // Call AI
        $ai = new AIClient();
        $result = $ai->chatWithHistory(AIClient::getChatPrompt(), $messagesForAI, 0.5);

        if (isset($result['error'])) {
            $responseText = json_encode(['response' => $result['error'], 'key_metrics' => [], 'action_items' => []]);
        } else {
            $responseText = json_encode($result, JSON_UNESCAPED_UNICODE);
        }

        // Save AI response
        $stmt = $pdo->prepare("INSERT INTO ai_chat_history (user_id, role, message, context_data) VALUES (?, 'assistant', ?, ?)");
        $stmt->execute([$userId, $responseText, json_encode($context)]);

        jsonResponse(['success' => true, 'data' => $result]);
    }

    if ($action === 'clear') {
        $stmt = $pdo->prepare("DELETE FROM ai_chat_history WHERE user_id = ?");
        $stmt->execute([$userId]);
        jsonResponse(['success' => true, 'message' => 'Chat history cleared']);
    }
}

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT id, role, message, created_at FROM ai_chat_history WHERE user_id = ? ORDER BY created_at ASC LIMIT 50");
    $stmt->execute([$userId]);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
