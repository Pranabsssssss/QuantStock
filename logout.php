<?php
/**
 * QuantStock — Logout Handler
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

logout();
header('Location: login.php');
exit;
