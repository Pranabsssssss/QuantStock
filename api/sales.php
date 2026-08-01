<?php
/**
 * QuantStock — Sales API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Product.php';

initApp();
requireApiAuth();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$saleModel = new Sale();

try {
    switch ($method) {
        case 'GET':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $sale = $saleModel->findById((int)$id);
                if (!$sale) jsonResponse(['success' => false, 'message' => 'Sale not found'], 404);
                jsonResponse(['success' => true, 'data' => $sale]);
            }

            $filters = [
                'search'    => $_GET['search'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to'   => $_GET['date_to'] ?? '',
                'status'    => $_GET['status'] ?? '',
                'limit'     => (int)($_GET['limit'] ?? 10),
                'offset'    => (int)($_GET['offset'] ?? 0),
            ];
            $result = $saleModel->getAll($filters);
            jsonResponse(['success' => true, 'data' => $result['data'], 'total' => $result['total']]);
            break;

        case 'POST':
            $data = getJsonInput();
            if (empty($data)) $data = $_POST;

            if (!validateCSRFToken($data['csrf_token'] ?? '')) {
                jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
            }

            if (empty($data['items']) || !is_array($data['items'])) {
                jsonResponse(['success' => false, 'message' => 'At least one item is required'], 400);
            }

            // Validate items
            $items = [];
            foreach ($data['items'] as $item) {
                if (empty($item['product_id']) || empty($item['quantity'])) continue;
                $items[] = [
                    'product_id' => (int)$item['product_id'],
                    'quantity'   => (int)$item['quantity'],
                    'unit_price' => !empty($item['unit_price']) ? (float)$item['unit_price'] : null,
                ];
            }

            if (empty($items)) {
                jsonResponse(['success' => false, 'message' => 'Valid sale items required'], 400);
            }

            $saleData = [
                'user_id'        => $_SESSION['user_id'],
                'customer_name'  => $data['customer_name'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'discount'       => (float)($data['discount'] ?? 0),
                'tax'            => (float)($data['tax'] ?? 0),
                'notes'          => $data['notes'] ?? null,
            ];

            $saleId = $saleModel->create($saleData, $items);
            $sale = $saleModel->findById($saleId);

            jsonResponse([
                'success' => true,
                'message' => 'Sale recorded successfully',
                'data'    => $sale,
            ]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
