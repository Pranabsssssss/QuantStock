<?php
/**
 * QuantStock — Auth API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

initApp();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = getJsonInput();
    $action = $data['action'] ?? '';

    if ($action === 'login') {
        $result = attemptLogin($data['email'] ?? '', $data['password'] ?? '');
        jsonResponse($result, $result['success'] ? 200 : 401);
    } elseif ($action === 'logout') {
        logout();
        jsonResponse(['success' => true, 'message' => 'Logged out']);
    }
}

if ($method === 'GET') {
    jsonResponse([
        'success' => true,
        'logged_in' => isLoggedIn(),
        'user' => getCurrentUser(),
    ]);
}

jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
