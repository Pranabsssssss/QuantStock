<?php
/**
 * QuantStock — Settings Model
 */

class Settings {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function get(string $key, string $default = ''): string {
        $stmt = $this->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    public function set(string $key, string $value): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value]);
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM settings ORDER BY setting_key");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function setMultiple(array $settings): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        return true;
    }
}
