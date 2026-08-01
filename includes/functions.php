<?php
/**
 * QuantStock — Helper Functions
 * 
 * Reusable utility functions for formatting, sanitization, and validation.
 */

/**
 * Format currency with symbol
 */
function formatCurrency(float $amount, ?string $symbol = null): string {
    if ($symbol === null) {
        $symbol = getSetting('currency', '₹');
    }
    return $symbol . number_format($amount, 2);
}

/**
 * Format number with commas
 */
function formatNumber(int|float $number): string {
    return number_format($number);
}

/**
 * Format compact number (1.2K, 3.5M)
 */
function formatCompact(float $number): string {
    if ($number >= 10000000) return '₹' . number_format($number / 10000000, 2) . 'Cr';
    if ($number >= 100000) return '₹' . number_format($number / 100000, 2) . 'L';
    if ($number >= 1000) return '₹' . number_format($number / 1000, 1) . 'K';
    return '₹' . number_format($number, 0);
}

/**
 * Format date
 */
function formatDate(string $date, string $format = 'd M Y'): string {
    return date($format, strtotime($date));
}

/**
 * Format date with time
 */
function formatDateTime(string $date): string {
    return date('d M Y, h:i A', strtotime($date));
}

/**
 * Get time ago string
 */
function timeAgo(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for HTML
 */
function e(mixed $value): string {
    if ($value === null) return '';
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate positive number
 */
function isPositiveNumber(mixed $value): bool {
    return is_numeric($value) && $value >= 0;
}

/**
 * Generate unique invoice number
 */
function generateInvoiceNumber(): string {
    $pdo = Database::getInstance();
    $prefix = 'INV-' . date('Ym');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE invoice_number LIKE ?");
    $stmt->execute([$prefix . '%']);
    $count = (int)$stmt->fetchColumn() + 1;
    return $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate SKU
 */
function generateSKU(string $name, int $categoryId = 0): string {
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
    if (strlen($prefix) < 3) $prefix = str_pad($prefix, 3, 'X');
    return $prefix . '-' . str_pad($categoryId, 2, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -4));
}

/**
 * Get pagination data
 */
function getPagination(int $totalItems, int $currentPage, int $perPage = 10): array {
    $totalPages = max(1, (int)ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'       => $totalItems,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
    ];
}

/**
 * Get percentage change
 */
function percentageChange(float $current, float $previous): array {
    if ($previous == 0) {
        return ['value' => $current > 0 ? 100 : 0, 'direction' => $current > 0 ? 'up' : 'stable'];
    }
    $change = (($current - $previous) / abs($previous)) * 100;
    return [
        'value'     => round(abs($change), 1),
        'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
    ];
}

/**
 * Get stock status
 */
function getStockStatus(int $current, int $min, int $max): array {
    if ($current <= 0) return ['label' => 'Out of Stock', 'color' => 'red', 'class' => 'danger'];
    if ($current <= $min) return ['label' => 'Low Stock', 'color' => 'orange', 'class' => 'warning'];
    if ($current >= $max) return ['label' => 'Overstock', 'color' => 'blue', 'class' => 'info'];
    return ['label' => 'In Stock', 'color' => 'green', 'class' => 'success'];
}

/**
 * Handle file upload
 */
function handleImageUpload(array $file, string $directory = 'products'): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    
    // Validate type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        throw new Exception('Invalid image type. Allowed: JPG, PNG, WebP');
    }
    
    // Validate size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new Exception('Image too large. Maximum: 2MB');
    }
    
    // Generate unique filename
    $ext = match($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };
    $filename = uniqid('img_') . '.' . $ext;
    $targetPath = UPLOAD_PATH . '/' . $directory . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to save uploaded image');
    }
    
    return 'uploads/' . $directory . '/' . $filename;
}

/**
 * Delete uploaded file
 */
function deleteUploadedFile(?string $path): void {
    if ($path && file_exists(BASE_PATH . '/' . $path)) {
        unlink(BASE_PATH . '/' . $path);
    }
}

/**
 * Get request input
 */
function getInput(string $key, mixed $default = null): mixed {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Get JSON body input
 */
function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
