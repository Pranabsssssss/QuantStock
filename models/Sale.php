<?php
/**
 * QuantStock — Sale Model
 */

class Sale {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Create a new sale with items — reduces inventory automatically
     */
    public function create(array $saleData, array $items): int {
        $this->pdo->beginTransaction();
        try {
            $invoiceNumber = generateInvoiceNumber();
            $totalAmount = 0;
            $totalCost = 0;

            // Calculate totals
            foreach ($items as &$item) {
                $product = $this->pdo->prepare("SELECT id, name, cost_price, selling_price, current_stock FROM products WHERE id = ? AND status = 'active' FOR UPDATE");
                $product->execute([$item['product_id']]);
                $prod = $product->fetch();

                if (!$prod) throw new Exception("Product #{$item['product_id']} not found");
                if ($prod['current_stock'] < $item['quantity']) throw new Exception("Insufficient stock for {$prod['name']}");

                $unitPrice = $item['unit_price'] ?? $prod['selling_price'];
                $item['unit_price'] = $unitPrice;
                $item['cost_price'] = $prod['cost_price'];
                $item['total_price'] = $unitPrice * $item['quantity'];
                $item['product_name'] = $prod['name'];
                $totalAmount += $item['total_price'];
                $totalCost += $prod['cost_price'] * $item['quantity'];
            }
            unset($item);

            $discount = (float)($saleData['discount'] ?? 0);
            $tax = (float)($saleData['tax'] ?? 0);
            $netAmount = $totalAmount - $discount + $tax;

            // Insert sale
            $stmt = $this->pdo->prepare("
                INSERT INTO sales (invoice_number, user_id, customer_name, total_amount, discount, tax, net_amount, payment_method, status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)
            ");
            $stmt->execute([
                $invoiceNumber,
                $saleData['user_id'],
                $saleData['customer_name'] ?? null,
                $totalAmount,
                $discount,
                $tax,
                $netAmount,
                $saleData['payment_method'] ?? 'cash',
                $saleData['notes'] ?? null,
            ]);
            $saleId = (int)$this->pdo->lastInsertId();

            // Insert sale items and reduce stock
            $itemStmt = $this->pdo->prepare("
                INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, cost_price, total_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stockStmt = $this->pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");
            $logStmt = $this->pdo->prepare("
                INSERT INTO inventory_logs (product_id, type, quantity, previous_stock, new_stock, reference, user_id)
                VALUES (?, 'out', ?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([$saleId, $item['product_id'], $item['quantity'], $item['unit_price'], $item['cost_price'], $item['total_price']]);
                
                // Get current stock before update
                $currStmt = $this->pdo->prepare("SELECT current_stock FROM products WHERE id = ?");
                $currStmt->execute([$item['product_id']]);
                $prevStock = (int)$currStmt->fetchColumn();
                
                $stockStmt->execute([$item['quantity'], $item['product_id']]);
                $newStock = $prevStock - $item['quantity'];
                $logStmt->execute([$item['product_id'], $item['quantity'], $prevStock, $newStock, "Sale #{$invoiceNumber}", $saleData['user_id']]);
            }

            $this->pdo->commit();
            return $saleId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getAll(array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(s.invoice_number LIKE ? OR s.customer_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search]);
        }
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(s.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(s.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['status'])) {
            $where[] = "s.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);
        $limit = (int)($filters['limit'] ?? 10);
        $offset = (int)($filters['offset'] ?? 0);

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM sales s WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            SELECT s.*, u.name as user_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE {$whereClause}
            ORDER BY s.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.name as user_name FROM sales s
            LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();
        if (!$sale) return null;

        $items = $this->pdo->prepare("
            SELECT si.*, p.name as product_name, p.sku, p.image
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = ?
        ");
        $items->execute([$id]);
        $sale['items'] = $items->fetchAll();
        return $sale;
    }

    public function getTodayRevenue(): float {
        return (float)$this->pdo->query("SELECT COALESCE(SUM(net_amount), 0) FROM sales WHERE DATE(created_at) = CURDATE() AND status = 'completed'")->fetchColumn();
    }

    public function getTodaySalesCount(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = CURDATE() AND status = 'completed'")->fetchColumn();
    }

    public function getMonthRevenue(): float {
        return (float)$this->pdo->query("SELECT COALESCE(SUM(net_amount), 0) FROM sales WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) AND status = 'completed'")->fetchColumn();
    }

    public function getMonthSalesCount(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM sales WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) AND status = 'completed'")->fetchColumn();
    }

    public function getRecentSales(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT s.id, s.invoice_number, s.net_amount, s.payment_method, s.created_at, s.customer_name,
                   GROUP_CONCAT(p.name SEPARATOR ', ') as products
            FROM sales s
            LEFT JOIN sale_items si ON s.id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.id
            WHERE s.status = 'completed'
            GROUP BY s.id
            ORDER BY s.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getSalesTrend(int $days = 30): array {
        $stmt = $this->pdo->prepare("
            SELECT DATE(created_at) as date, 
                   COUNT(*) as sales_count,
                   COALESCE(SUM(net_amount), 0) as revenue
            FROM sales 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND status = 'completed'
            GROUP BY DATE(created_at) 
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getPreviousMonthRevenue(): float {
        return (float)$this->pdo->query("
            SELECT COALESCE(SUM(net_amount), 0) FROM sales 
            WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
            AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
            AND status = 'completed'
        ")->fetchColumn();
    }

    public function getForAI(): array {
        $stmt = $this->pdo->query("
            SELECT DATE(s.created_at) as sale_date,
                   p.id as product_id, p.name as product_name,
                   si.quantity, si.unit_price, si.total_price
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE s.status = 'completed' AND s.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            ORDER BY s.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
