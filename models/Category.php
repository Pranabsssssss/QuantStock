<?php
/**
 * QuantStock — Category Model
 */

class Category {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll(bool $activeOnly = false): array {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        $stmt = $this->pdo->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            {$where}
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, description, color, icon) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['description'] ?? null, $data['color'] ?? '#3B82F6', $data['icon'] ?? 'package']);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, description = ?, color = ?, icon = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['description'] ?? null, $data['color'] ?? '#3B82F6', $data['icon'] ?? 'package', $id]);
    }

    public function delete(int $id): bool {
        // Check if products exist
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false; // Cannot delete category with products
        }
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
