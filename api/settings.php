<?php
/**
 * QuantStock — Settings API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/User.php';

initApp();
requireApiAuth();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$settingsModel = new Settings();

if ($method === 'GET') {
    jsonResponse(['success' => true, 'data' => $settingsModel->getAll()]);
}

if ($method === 'POST') {
    $data = getJsonInput();
    if (empty($data)) $data = $_POST;
    
    $action = $data['action'] ?? 'update';

    if ($action === 'update') {
        $allowed = ['business_name', 'currency', 'currency_code', 'timezone', 'theme',
                     'ai_api_key', 'ai_provider', 'ai_model', 'prediction_frequency', 'low_stock_threshold'];
        
        $toUpdate = [];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $toUpdate[$key] = $data[$key];
            }
        }

        if (!empty($toUpdate)) {
            $settingsModel->setMultiple($toUpdate);
        }

        jsonResponse(['success' => true, 'message' => 'Settings saved successfully']);
    }

    if ($action === 'update_profile') {
        $userModel = new User();
        $userId = $_SESSION['user_id'];
        
        $profileData = [];
        if (!empty($data['name'])) $profileData['name'] = sanitize($data['name']);
        if (!empty($data['email']) && isValidEmail($data['email'])) $profileData['email'] = $data['email'];

        if (!empty($profileData)) {
            $userModel->updateProfile($userId, $profileData);
            // Update session
            if (isset($profileData['name'])) $_SESSION['user']['name'] = $profileData['name'];
            if (isset($profileData['email'])) $_SESSION['user']['email'] = $profileData['email'];
        }

        // Password change
        if (!empty($data['new_password'])) {
            if (strlen($data['new_password']) < 6) {
                jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
            }
            if ($data['new_password'] !== ($data['confirm_password'] ?? '')) {
                jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
            }
            $userModel->updatePassword($userId, $data['new_password']);
        }

        jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
