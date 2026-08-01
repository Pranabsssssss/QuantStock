<?php
/**
 * QuantStock — Supplier Model
 */

class Supplier {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll(bool $activeOnly = false): array {
        $where = $activeOnly ? "WHERE s.status = 'active'" : "";
        $stmt = $this->pdo->query("
            SELECT s.*, COUNT(p.id) as product_count
            FROM suppliers s
            LEFT JOIN products p ON s.id = p.supplier_id AND p.status = 'active'
            {$where}
            GROUP BY s.id
            ORDER BY s.name ASC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO suppliers (name, email, phone, address, city, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'] ?? null, $data['phone'] ?? null, $data['address'] ?? null, $data['city'] ?? null, $data['status'] ?? 'active']);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE suppliers SET name = ?, email = ?, phone = ?, address = ?, city = ?, status = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['email'] ?? null, $data['phone'] ?? null, $data['address'] ?? null, $data['city'] ?? null, $data['status'] ?? 'active', $id]);
    }

    public function delete(int $id): bool {
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE supplier_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) return false;
        $stmt = $this->pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
