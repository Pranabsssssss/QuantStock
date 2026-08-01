<?php
/**
 * QuantStock — Product Model
 */

class Product {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll(array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search]);
        }
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['supplier_id'])) {
            $where[] = "p.supplier_id = ?";
            $params[] = $filters['supplier_id'];
        }
        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $where[] = "p.current_stock <= p.min_stock";
        }

        $orderBy = $filters['sort'] ?? 'p.created_at';
        $orderDir = ($filters['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $limit = (int)($filters['limit'] ?? 10);
        $offset = (int)($filters['offset'] ?? 0);

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countSql = "SELECT COUNT(*) FROM products p WHERE {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Get data
        $sql = "SELECT p.*, c.name as category_name, c.color as category_color, s.name as supplier_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE {$whereClause}
                ORDER BY {$orderBy} {$orderDir}
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name as category_name, s.name as supplier_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (name, sku, barcode, description, category_id, supplier_id, 
                                  cost_price, selling_price, current_stock, min_stock, max_stock, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'], $data['sku'], $data['barcode'] ?? null, $data['description'] ?? null,
            $data['category_id'] ?: null, $data['supplier_id'] ?: null,
            $data['cost_price'], $data['selling_price'],
            $data['current_stock'] ?? 0, $data['min_stock'] ?? 5, $data['max_stock'] ?? 1000,
            $data['image'] ?? null, $data['status'] ?? 'active',
        ]);

        $productId = (int)$this->pdo->lastInsertId();

        // Log inventory
        if (($data['current_stock'] ?? 0) > 0) {
            $this->logInventory($productId, 'in', $data['current_stock'], 0, $data['current_stock'], 'Initial stock', $data['user_id'] ?? null);
        }

        return $productId;
    }

    public function update(int $id, array $data): bool {
        $product = $this->findById($id);
        if (!$product) return false;

        $fields = [];
        $values = [];
        $allowed = ['name', 'sku', 'barcode', 'description', 'category_id', 'supplier_id',
                     'cost_price', 'selling_price', 'current_stock', 'min_stock', 'max_stock', 'image', 'status'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`{$field}` = ?";
                $values[] = $data[$field] === '' ? null : $data[$field];
            }
        }

        if (empty($fields)) return false;
        $values[] = $id;

        $stmt = $this->pdo->prepare("UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?");
        $result = $stmt->execute($values);

        // Log stock change if applicable
        if (isset($data['current_stock']) && (int)$data['current_stock'] !== (int)$product['current_stock']) {
            $diff = (int)$data['current_stock'] - (int)$product['current_stock'];
            $type = $diff > 0 ? 'in' : 'adjustment';
            $this->logInventory($id, $type, abs($diff), $product['current_stock'], $data['current_stock'], 'Stock update', $data['user_id'] ?? null);
        }

        return $result;
    }

    public function delete(int $id): bool {
        // Check if product has sales
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM sale_items WHERE product_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            // Soft delete — mark as discontinued
            $stmt = $this->pdo->prepare("UPDATE products SET status = 'discontinued' WHERE id = ?");
            return $stmt->execute([$id]);
        }
        
        $product = $this->findById($id);
        if ($product && $product['image']) {
            deleteUploadedFile($product['image']);
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getCount(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    }

    public function getTotalValue(): float {
        return (float)$this->pdo->query("SELECT COALESCE(SUM(current_stock * selling_price), 0) FROM products WHERE status = 'active'")->fetchColumn();
    }

    public function getCostValue(): float {
        return (float)$this->pdo->query("SELECT COALESCE(SUM(current_stock * cost_price), 0) FROM products WHERE status = 'active'")->fetchColumn();
    }

    public function getLowStock(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name as category_name, c.color as category_color
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.current_stock <= p.min_stock AND p.status = 'active'
            ORDER BY (p.current_stock / GREATEST(p.min_stock, 1)) ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getLowStockCount(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM products WHERE current_stock <= min_stock AND status = 'active'")->fetchColumn();
    }

    public function getTopSelling(int $days = 30, int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.sku, p.image, p.current_stock, p.selling_price,
                   COALESCE(SUM(si.quantity), 0) as total_sold,
                   COALESCE(SUM(si.total_price), 0) as total_revenue
            FROM products p
            LEFT JOIN sale_items si ON p.id = si.product_id
            LEFT JOIN sales s ON si.sale_id = s.id AND s.status = 'completed' AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE p.status = 'active'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$days, $limit]);
        return $stmt->fetchAll();
    }

    public function getForAI(): array {
        $stmt = $this->pdo->query("
            SELECT p.id, p.name, p.sku, p.cost_price, p.selling_price, 
                   p.current_stock, p.min_stock, p.max_stock,
                   c.name as category, s.name as supplier,
                   COALESCE(sales_data.total_sold_30d, 0) as sold_last_30d,
                   COALESCE(sales_data.total_sold_7d, 0) as sold_last_7d,
                   COALESCE(sales_data.avg_daily_sales, 0) as avg_daily_sales
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN (
                SELECT si.product_id,
                       SUM(CASE WHEN s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN si.quantity ELSE 0 END) as total_sold_30d,
                       SUM(CASE WHEN s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN si.quantity ELSE 0 END) as total_sold_7d,
                       COALESCE(SUM(si.quantity) / GREATEST(DATEDIFF(NOW(), MIN(s.created_at)), 1), 0) as avg_daily_sales
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id AND s.status = 'completed'
                GROUP BY si.product_id
            ) as sales_data ON p.id = sales_data.product_id
            WHERE p.status = 'active'
            ORDER BY p.name
        ");
        return $stmt->fetchAll();
    }

    private function logInventory(int $productId, string $type, int $quantity, int $prevStock, int $newStock, string $ref, ?int $userId): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO inventory_logs (product_id, type, quantity, previous_stock, new_stock, reference, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$productId, $type, $quantity, $prevStock, $newStock, $ref, $userId]);
    }

    public function getDistribution(): array {
        $stmt = $this->pdo->query("
            SELECT c.name, c.color, COUNT(p.id) as count, COALESCE(SUM(p.current_stock), 0) as total_stock
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY count DESC
        ");
        return $stmt->fetchAll();
    }
}
