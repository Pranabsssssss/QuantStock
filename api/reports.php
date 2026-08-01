<?php
/**
 * QuantStock — Reports API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/Forecast.php';
require_once __DIR__ . '/../models/Category.php';

initApp();
requireApiAuth();

$type = $_GET['type'] ?? 'inventory';
$format = $_GET['format'] ?? 'json';
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

$pdo = Database::getInstance();

try {
    switch ($type) {
        case 'inventory':
            $stmt = $pdo->query("
                SELECT p.name, p.sku, p.barcode, c.name as category, s.name as supplier,
                       p.cost_price, p.selling_price, p.current_stock, p.min_stock, p.max_stock,
                       (p.current_stock * p.selling_price) as stock_value,
                       p.status
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.status != 'discontinued'
                ORDER BY p.name
            ");
            $data = $stmt->fetchAll();
            $title = 'Inventory Report';
            $columns = ['Name', 'SKU', 'Barcode', 'Category', 'Supplier', 'Cost Price', 'Selling Price', 'Stock', 'Min', 'Max', 'Value', 'Status'];
            break;

        case 'sales':
            $stmt = $pdo->prepare("
                SELECT s.invoice_number, s.customer_name, s.total_amount, s.discount, s.tax, s.net_amount,
                       s.payment_method, s.status, s.created_at, u.name as sold_by,
                       GROUP_CONCAT(CONCAT(p.name, ' x', si.quantity) SEPARATOR '; ') as items
                FROM sales s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN sale_items si ON s.id = si.sale_id
                LEFT JOIN products p ON si.product_id = p.id
                WHERE DATE(s.created_at) BETWEEN ? AND ?
                GROUP BY s.id
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$dateFrom, $dateTo]);
            $data = $stmt->fetchAll();
            $title = 'Sales Report';
            $columns = ['Invoice', 'Customer', 'Items', 'Total', 'Discount', 'Tax', 'Net Amount', 'Payment', 'Status', 'Date', 'Sold By'];
            break;

        case 'forecast':
            $stmt = $pdo->query("
                SELECT fh.created_at as generated_on, p.name as product, p.sku, p.current_stock,
                       fh.forecast_type, fh.period_days, fh.predicted_demand, fh.confidence
                FROM forecast_history fh
                LEFT JOIN products p ON fh.product_id = p.id
                ORDER BY fh.created_at DESC
                LIMIT 100
            ");
            $data = $stmt->fetchAll();
            $title = 'Forecast Report';
            $columns = ['Generated', 'Product', 'SKU', 'Current Stock', 'Type', 'Period (Days)', 'Predicted Demand', 'Confidence'];
            break;

        default:
            header('Content-Type: application/json');
            jsonResponse(['success' => false, 'message' => 'Unknown report type'], 400);
    }

    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel
        
        fputcsv($output, $columns);
        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }
        fclose($output);
        exit;
    }

    // JSON format
    header('Content-Type: application/json');
    jsonResponse([
        'success' => true,
        'title'   => $title,
        'columns' => $columns,
        'data'    => $data,
        'generated_at' => date('Y-m-d H:i:s'),
        'date_range' => ['from' => $dateFrom, 'to' => $dateTo],
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
