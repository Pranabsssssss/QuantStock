<?php
/**
 * QuantStock — User Model
 */

class User {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id, name, email, avatar, role, last_login, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $id, array $data): bool {
        $fields = [];
        $values = [];
        
        foreach (['name', 'email', 'avatar'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "`{$field}` = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        $values[] = $id;
        
        $stmt = $this->pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }
}
