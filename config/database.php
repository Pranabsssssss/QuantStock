<?php
/**
 * QuantStock — Database Configuration
 * 
 * PDO connection with auto-table creation.
 * All 11 tables with proper foreign keys, indexes, and constraints.
 */

class Database {
    private static ?PDO $instance = null;
    
    private const HOST = 'srv831.hstgr.io';
    private const DB_NAME = 'u672633758_quantstock';
    private const USERNAME = 'u672633758_qs';
    private const PASSWORD = 'QuantIsStocking007';
    private const CHARSET = 'utf8mb4';

    /**
     * Get singleton PDO instance
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    self::HOST,
                    self::DB_NAME,
                    self::CHARSET
                );
                
                self::$instance = new PDO($dsn, self::USERNAME, self::PASSWORD, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                die(json_encode([
                    'success' => false, 
                    'message' => 'Database connection failed. Please check your credentials.'
                ]));
            }
        }
        return self::$instance;
    }

    /**
     * Initialize all database tables
     */
    public static function initialize(): void {
        $pdo = self::getInstance();
        
        // Users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `avatar` VARCHAR(255) DEFAULT NULL,
                `role` ENUM('admin', 'manager', 'staff') NOT NULL DEFAULT 'admin',
                `last_login` DATETIME DEFAULT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_users_email` (`email`),
                INDEX `idx_users_role` (`role`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Categories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `description` TEXT DEFAULT NULL,
                `color` VARCHAR(7) DEFAULT '#3B82F6',
                `icon` VARCHAR(50) DEFAULT 'package',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_categories_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Suppliers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `suppliers` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(150) NOT NULL,
                `email` VARCHAR(255) DEFAULT NULL,
                `phone` VARCHAR(20) DEFAULT NULL,
                `address` TEXT DEFAULT NULL,
                `city` VARCHAR(100) DEFAULT NULL,
                `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_suppliers_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Products table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `products` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(200) NOT NULL,
                `sku` VARCHAR(50) NOT NULL UNIQUE,
                `barcode` VARCHAR(50) DEFAULT NULL,
                `description` TEXT DEFAULT NULL,
                `category_id` INT UNSIGNED DEFAULT NULL,
                `supplier_id` INT UNSIGNED DEFAULT NULL,
                `cost_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `selling_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `current_stock` INT NOT NULL DEFAULT 0,
                `min_stock` INT NOT NULL DEFAULT 5,
                `max_stock` INT NOT NULL DEFAULT 1000,
                `image` VARCHAR(255) DEFAULT NULL,
                `status` ENUM('active', 'inactive', 'discontinued') NOT NULL DEFAULT 'active',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_products_category` (`category_id`),
                INDEX `idx_products_supplier` (`supplier_id`),
                INDEX `idx_products_status` (`status`),
                INDEX `idx_products_sku` (`sku`),
                INDEX `idx_products_stock` (`current_stock`),
                CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk_products_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Sales table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sales` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `invoice_number` VARCHAR(30) NOT NULL UNIQUE,
                `user_id` INT UNSIGNED NOT NULL,
                `customer_name` VARCHAR(150) DEFAULT NULL,
                `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `tax` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `net_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `payment_method` ENUM('cash', 'card', 'upi', 'bank_transfer', 'other') NOT NULL DEFAULT 'cash',
                `status` ENUM('completed', 'pending', 'cancelled', 'refunded') NOT NULL DEFAULT 'completed',
                `notes` TEXT DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_sales_user` (`user_id`),
                INDEX `idx_sales_status` (`status`),
                INDEX `idx_sales_date` (`created_at`),
                INDEX `idx_sales_invoice` (`invoice_number`),
                CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Sale items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sale_items` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `sale_id` INT UNSIGNED NOT NULL,
                `product_id` INT UNSIGNED NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `unit_price` DECIMAL(12,2) NOT NULL,
                `cost_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `total_price` DECIMAL(12,2) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_sale_items_sale` (`sale_id`),
                INDEX `idx_sale_items_product` (`product_id`),
                CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Inventory logs table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `inventory_logs` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT UNSIGNED NOT NULL,
                `type` ENUM('in', 'out', 'adjustment', 'return') NOT NULL,
                `quantity` INT NOT NULL,
                `previous_stock` INT NOT NULL DEFAULT 0,
                `new_stock` INT NOT NULL DEFAULT 0,
                `reference` VARCHAR(100) DEFAULT NULL,
                `notes` TEXT DEFAULT NULL,
                `user_id` INT UNSIGNED DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_inventory_logs_product` (`product_id`),
                INDEX `idx_inventory_logs_type` (`type`),
                INDEX `idx_inventory_logs_date` (`created_at`),
                CONSTRAINT `fk_inventory_logs_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_inventory_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Forecast history table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `forecast_history` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT UNSIGNED DEFAULT NULL,
                `forecast_type` ENUM('demand', 'revenue', 'stock') NOT NULL DEFAULT 'demand',
                `period_days` INT NOT NULL DEFAULT 7,
                `predicted_demand` DECIMAL(12,2) DEFAULT NULL,
                `confidence` DECIMAL(5,2) DEFAULT NULL,
                `data_json` JSON DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_forecast_product` (`product_id`),
                INDEX `idx_forecast_type` (`forecast_type`),
                INDEX `idx_forecast_date` (`created_at`),
                CONSTRAINT `fk_forecast_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // AI predictions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ai_predictions` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `type` ENUM('forecast', 'optimization', 'risk', 'recommendation', 'analysis') NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `summary` TEXT DEFAULT NULL,
                `data_json` JSON DEFAULT NULL,
                `status` ENUM('active', 'dismissed', 'applied', 'expired') NOT NULL DEFAULT 'active',
                `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expires_at` DATETIME DEFAULT NULL,
                INDEX `idx_predictions_type` (`type`),
                INDEX `idx_predictions_status` (`status`),
                INDEX `idx_predictions_date` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // AI chat history table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ai_chat_history` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `role` ENUM('user', 'assistant', 'system') NOT NULL,
                `message` TEXT NOT NULL,
                `context_data` JSON DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_chat_user` (`user_id`),
                INDEX `idx_chat_date` (`created_at`),
                CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Settings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT DEFAULT NULL,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_settings_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert default settings if not exist
        $defaults = [
            ['ai_api_key', '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'],
            ['ai_provider', 'groq'],
            ['ai_model', 'llama-3.3-70b-versatile'],
            ['business_name', 'QuantStock'],
            ['currency', '₹'],
            ['currency_code', 'INR'],
            ['timezone', 'Asia/Kolkata'],
            ['theme', 'dark'],
            ['prediction_frequency', 'daily'],
            ['low_stock_threshold', '10'],
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");
        foreach ($defaults as $setting) {
            $stmt->execute($setting);
        }

        // Create default admin user if not exists
        $check = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
        if ($check == 0) {
            $hash = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Pranab', 'admin@quantstock.ai', $hash, 'admin']);
        }
    }
}
