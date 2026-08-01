<?php
/**
 * QuantStock — Forecast Model
 */

class Forecast {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function savePrediction(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO ai_predictions (type, title, summary, data_json, status, priority, expires_at)
            VALUES (?, ?, ?, ?, 'active', ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        $stmt->execute([
            $data['type'], $data['title'], $data['summary'],
            json_encode($data['data']), $data['priority'] ?? 'medium'
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function saveProductForecast(int $productId, array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO forecast_history (product_id, forecast_type, period_days, predicted_demand, confidence, data_json)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $productId,
            $data['forecast_type'] ?? 'demand',
            $data['period_days'] ?? 30,
            $data['predicted_demand'] ?? 0,
            $data['confidence'] ?? 0,
            json_encode($data['details'] ?? []),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getLatestPredictions(string $type = '', int $limit = 10): array {
        $where = "WHERE status = 'active'";
        $params = [];
        if ($type) {
            $where .= " AND type = ?";
            $params[] = $type;
        }
        $stmt = $this->pdo->prepare("
            SELECT * FROM ai_predictions {$where} ORDER BY created_at DESC LIMIT {$limit}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getProductForecasts(int $productId = 0, int $limit = 20): array {
        $where = $productId ? "WHERE fh.product_id = ?" : "";
        $params = $productId ? [$productId] : [];
        $stmt = $this->pdo->prepare("
            SELECT fh.*, p.name as product_name, p.sku
            FROM forecast_history fh
            LEFT JOIN products p ON fh.product_id = p.id
            {$where}
            ORDER BY fh.created_at DESC LIMIT {$limit}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function dismissPrediction(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE ai_predictions SET status = 'dismissed' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function applyPrediction(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE ai_predictions SET status = 'applied' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function clearOldPredictions(): int {
        $stmt = $this->pdo->exec("UPDATE ai_predictions SET status = 'expired' WHERE expires_at < NOW() AND status = 'active'");
        return (int)$stmt;
    }
}
