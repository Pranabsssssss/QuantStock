<?php
/**
 * QuantStock — Analytics Model
 */

class Analytics {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getRevenueTrend(int $months = 6): array {
        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                   DATE_FORMAT(created_at, '%b %Y') as label,
                   COALESCE(SUM(net_amount), 0) as revenue,
                   COUNT(*) as orders
            FROM sales WHERE status = 'completed' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY month ORDER BY month ASC
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public function getProfitTrend(int $months = 6): array {
        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(s.created_at, '%Y-%m') as month,
                   DATE_FORMAT(s.created_at, '%b %Y') as label,
                   COALESCE(SUM(si.total_price - (si.cost_price * si.quantity)), 0) as profit,
                   COALESCE(SUM(si.total_price), 0) as revenue,
                   COALESCE(SUM(si.cost_price * si.quantity), 0) as cost
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            WHERE s.status = 'completed' AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY month ORDER BY month ASC
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public function getTopProducts(int $days = 30, int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.sku, p.image, p.current_stock,
                   SUM(si.quantity) as total_sold,
                   SUM(si.total_price) as total_revenue,
                   SUM(si.total_price - (si.cost_price * si.quantity)) as profit
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            JOIN sales s ON si.sale_id = s.id
            WHERE s.status = 'completed' AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY p.id ORDER BY total_sold DESC LIMIT ?
        ");
        $stmt->execute([$days, $limit]);
        return $stmt->fetchAll();
    }

    public function getSlowMovingProducts(int $days = 30, int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.sku, p.current_stock, p.selling_price,
                   (p.current_stock * p.selling_price) as stock_value,
                   COALESCE(SUM(si.quantity), 0) as total_sold
            FROM products p
            LEFT JOIN sale_items si ON p.id = si.product_id
            LEFT JOIN sales s ON si.sale_id = s.id AND s.status = 'completed' AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE p.status = 'active' AND p.current_stock > 0
            GROUP BY p.id
            HAVING total_sold <= 2
            ORDER BY stock_value DESC
            LIMIT ?
        ");
        $stmt->execute([$days, $limit]);
        return $stmt->fetchAll();
    }

    public function getDeadStock(int $days = 60): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.sku, p.current_stock, p.cost_price, p.selling_price,
                   (p.current_stock * p.cost_price) as locked_value,
                   MAX(s.created_at) as last_sold
            FROM products p
            LEFT JOIN sale_items si ON p.id = si.product_id
            LEFT JOIN sales s ON si.sale_id = s.id AND s.status = 'completed'
            WHERE p.status = 'active' AND p.current_stock > 0
            GROUP BY p.id
            HAVING last_sold IS NULL OR last_sold < DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY locked_value DESC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getCategoryPerformance(int $days = 30): array {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.name, c.color,
                   COUNT(DISTINCT p.id) as product_count,
                   COALESCE(SUM(si.quantity), 0) as total_sold,
                   COALESCE(SUM(si.total_price), 0) as revenue
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            LEFT JOIN sale_items si ON p.id = si.product_id
            LEFT JOIN sales s ON si.sale_id = s.id AND s.status = 'completed' AND s.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE c.is_active = 1
            GROUP BY c.id ORDER BY revenue DESC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getTotalRevenue(): float {
        return (float)$this->pdo->query("SELECT COALESCE(SUM(net_amount), 0) FROM sales WHERE status = 'completed'")->fetchColumn();
    }

    public function getTotalProfit(): float {
        return (float)$this->pdo->query("
            SELECT COALESCE(SUM(si.total_price - (si.cost_price * si.quantity)), 0)
            FROM sale_items si JOIN sales s ON si.sale_id = s.id WHERE s.status = 'completed'
        ")->fetchColumn();
    }

    public function getInventoryHealthScore(): int {
        $total = (int)$this->pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
        if ($total === 0) return 100;
        
        $healthy = (int)$this->pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active' AND current_stock > min_stock AND current_stock < max_stock")->fetchColumn();
        return (int)round(($healthy / $total) * 100);
    }

    public function getRecentActivity(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT il.*, p.name as product_name, u.name as user_name
            FROM inventory_logs il
            JOIN products p ON il.product_id = p.id
            LEFT JOIN users u ON il.user_id = u.id
            ORDER BY il.created_at DESC LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getDashboardSummary(): array {
        $productModel = new Product();
        $saleModel = new Sale();

        $monthRevenue = $saleModel->getMonthRevenue();
        $prevMonthRevenue = $saleModel->getPreviousMonthRevenue();
        $revenueChange = percentageChange($monthRevenue, $prevMonthRevenue);

        return [
            'total_products'    => $productModel->getCount(),
            'inventory_value'   => $productModel->getTotalValue(),
            'today_revenue'     => $saleModel->getTodayRevenue(),
            'today_sales'       => $saleModel->getTodaySalesCount(),
            'low_stock_count'   => $productModel->getLowStockCount(),
            'inventory_health'  => $this->getInventoryHealthScore(),
            'month_revenue'     => $monthRevenue,
            'month_sales'       => $saleModel->getMonthSalesCount(),
            'revenue_change'    => $revenueChange,
            'pending_recommendations' => $this->getPendingRecommendations(),
        ];
    }

    public function getPendingRecommendations(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM ai_predictions WHERE status = 'active'")->fetchColumn();
    }
}
