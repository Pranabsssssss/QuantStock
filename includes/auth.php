<?php
/**
 * QuantStock — Authentication Middleware
 * 
 * Login, logout, session management, route protection.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

/**
 * Attempt to log in a user
 */
function attemptLogin(string $email, string $password): array {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Update last login
    $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $update->execute([$user['id']]);

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
        'avatar'=> $user['avatar'],
    ];

    // Regenerate session ID for security
    session_regenerate_id(true);

    return ['success' => true, 'message' => 'Login successful.'];
}

/**
 * Log out the current user
 */
function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Initialize the application — call on every page load
 */
function initApp(): void {
    // Initialize database tables
    Database::initialize();
}
