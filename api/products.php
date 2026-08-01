<?php
/**
 * QuantStock — Products API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Supplier.php';

initApp();
requireApiAuth();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$productModel = new Product();

try {
    switch ($method) {
        case 'GET':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $product = $productModel->findById((int)$id);
                if (!$product) jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
                jsonResponse(['success' => true, 'data' => $product]);
            }
            
            $filters = [
                'search'      => $_GET['search'] ?? '',
                'category_id' => $_GET['category_id'] ?? '',
                'status'      => $_GET['status'] ?? '',
                'supplier_id' => $_GET['supplier_id'] ?? '',
                'low_stock'   => $_GET['low_stock'] ?? false,
                'sort'        => $_GET['sort'] ?? 'p.created_at',
                'order'       => $_GET['order'] ?? 'DESC',
                'limit'       => (int)($_GET['limit'] ?? 10),
                'offset'      => (int)($_GET['offset'] ?? 0),
            ];
            $result = $productModel->getAll($filters);
            jsonResponse(['success' => true, 'data' => $result['data'], 'total' => $result['total']]);
            break;

        case 'POST':
            // Handle both form data and JSON
            if (!empty($_FILES['image'])) {
                // Form submission with file
                $data = $_POST;
            } else {
                $data = getJsonInput();
                if (empty($data)) $data = $_POST;
            }

            if (!validateCSRFToken($data['csrf_token'] ?? '')) {
                jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
            }

            // Validation
            if (empty($data['name'])) jsonResponse(['success' => false, 'message' => 'Product name is required'], 400);
            if (!isset($data['cost_price']) || !isPositiveNumber($data['cost_price'])) jsonResponse(['success' => false, 'message' => 'Valid cost price is required'], 400);
            if (!isset($data['selling_price']) || !isPositiveNumber($data['selling_price'])) jsonResponse(['success' => false, 'message' => 'Valid selling price is required'], 400);

            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = generateSKU($data['name'], (int)($data['category_id'] ?? 0));
            }

            // Handle image upload
            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $data['image'] = handleImageUpload($_FILES['image']);
            }

            $data['user_id'] = $_SESSION['user_id'];

            if (!empty($data['id'])) {
                // Update
                $id = (int)$data['id'];
                $productModel->update($id, $data);
                jsonResponse(['success' => true, 'message' => 'Product updated successfully', 'id' => $id]);
            } else {
                // Create
                $id = $productModel->create($data);
                jsonResponse(['success' => true, 'message' => 'Product created successfully', 'id' => $id]);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
            
            $result = $productModel->delete((int)$id);
            if ($result) {
                jsonResponse(['success' => true, 'message' => 'Product deleted successfully']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Cannot delete product with sales history. It has been marked as discontinued.']);
            }
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
