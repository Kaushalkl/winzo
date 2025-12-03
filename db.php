<?php
// =============================
// ✅ ENHANCED DATABASE CONFIGURATION
// =============================

class DatabaseConfig {
    private $DB_HOST = "localhost";
    private $DB_PORT = 3306;
    private $DB_USER = "root";
    private $DB_PASS = "";
    private $DB_NAME = "wizo";
    public $conn;

    public function __construct() {
        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        try {
            // ---- Connect to MySQL ----
            $this->conn = new mysqli($this->DB_HOST, $this->DB_USER, $this->DB_PASS, "", $this->DB_PORT);
            
            if ($this->conn->connect_error) {
                throw new Exception("Database connection failed: " . $this->conn->connect_error);
            }
            
            // Set character set
            $this->conn->set_charset("utf8mb4");

            // ---- Create Database if not exists ----
            $this->createDatabase();
            
            // ---- Select Database ----
            $this->conn->select_db($this->DB_NAME);

            // ---- Create Tables ----
            $this->createTables();

            // ---- Insert default data ----
            $this->insertDefaultData();

            // ---- Database Optimizations ----
           

        } catch (Exception $e) {
            error_log("Database Initialization Error: " . $e->getMessage());
            die("❌ System initialization failed. Please try again later.");
        }
    }

    private function createDatabase() {
        $sql = "CREATE DATABASE IF NOT EXISTS `{$this->DB_NAME}` 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        if (!$this->conn->query($sql)) {
            throw new Exception("Database creation failed: " . $this->conn->error);
        }
    }

    private function createTables() {
        $tables = [
            'users' => $this->getUsersTableSQL(),
            'transactions' => $this->getTransactionsTableSQL(),
            'razorpay_orders' => $this->getRazorpayOrdersTableSQL(),
            'withdrawal_requests' => $this->getWithdrawalRequestsTableSQL(),
            'login_attempts' => $this->getLoginAttemptsTableSQL(),
            'user_sessions' => $this->getUserSessionsTableSQL(),
            'audit_log' => $this->getAuditLogTableSQL()
        ];

        foreach ($tables as $tableName => $sql) {
            if (!$this->conn->query($sql)) {
                // Only throw exception for critical tables
                if (in_array($tableName, ['users', 'transactions'])) {
                    throw new Exception("$tableName table creation failed: " . $this->conn->error);
                } else {
                    error_log("⚠️ $tableName table creation failed: " . $this->conn->error);
                }
            }
        }
    }

    private function getUsersTableSQL() {
        return "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            otp VARCHAR(10) DEFAULT NULL,
            otp_expiry DATETIME DEFAULT NULL,
            status ENUM('pending','active','suspended') DEFAULT 'pending',
            wallet_balance DECIMAL(12,2) DEFAULT 0.00,
            bank_name VARCHAR(255) DEFAULT NULL,
            bank_ifsc VARCHAR(20) DEFAULT NULL,
            account_number VARCHAR(50) DEFAULT NULL,
            account_holder VARCHAR(255) DEFAULT NULL,
            last_login DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_phone (phone)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private function getTransactionsTableSQL() {
        return "CREATE TABLE IF NOT EXISTS transactions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            type ENUM('credit','debit') NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            remark TEXT,
            razorpay_payment_id VARCHAR(255) DEFAULT NULL,
            razorpay_order_id VARCHAR(255) DEFAULT NULL,
            status ENUM('pending','success','failed','cancelled') DEFAULT 'pending',
            transaction_hash VARCHAR(64) UNIQUE DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            INDEX idx_status (status),
            INDEX idx_transaction_hash (transaction_hash),
            INDEX idx_razorpay_payment_id (razorpay_payment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private function getRazorpayOrdersTableSQL() {
        return "CREATE TABLE IF NOT EXISTS razorpay_orders (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            order_id VARCHAR(255) NOT NULL UNIQUE,
            user_id INT UNSIGNED NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            currency VARCHAR(10) DEFAULT 'INR',
            status ENUM('created','paid','failed','cancelled') DEFAULT 'created',
            receipt VARCHAR(255) DEFAULT NULL,
            razorpay_payment_id VARCHAR(255) DEFAULT NULL,
            payment_method VARCHAR(50) DEFAULT NULL,
            bank_name VARCHAR(100) DEFAULT NULL,
            wallet_name VARCHAR(100) DEFAULT NULL,
            vpa VARCHAR(100) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            contact VARCHAR(20) DEFAULT NULL,
            fee DECIMAL(10,2) DEFAULT 0.00,
            tax DECIMAL(10,2) DEFAULT 0.00,
            error_code VARCHAR(50) DEFAULT NULL,
            error_description TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_order_id (order_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_razorpay_payment_id (razorpay_payment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private function getWithdrawalRequestsTableSQL() {
        return "CREATE TABLE IF NOT EXISTS withdrawal_requests (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            bank_name VARCHAR(255) NOT NULL,
            account_number VARCHAR(50) NOT NULL,
            account_holder VARCHAR(255) NOT NULL,
            ifsc_code VARCHAR(20) NOT NULL,
            status ENUM('pending','processed','failed','cancelled') DEFAULT 'pending',
            transaction_id VARCHAR(100) DEFAULT NULL,
            admin_notes TEXT DEFAULT NULL,
            processed_by INT UNSIGNED DEFAULT NULL,
            processed_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private function getLoginAttemptsTableSQL() {
        return "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            username VARCHAR(100) NOT NULL,
            attempts INT DEFAULT 1,
            last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
            locked_until DATETIME DEFAULT NULL,
            INDEX idx_ip_address (ip_address),
            INDEX idx_username (username),
            INDEX idx_locked_until (locked_until),
            INDEX idx_last_attempt (last_attempt)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private function getUserSessionsTableSQL() {
        return "CREATE TABLE IF NOT EXISTS user_sessions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            session_id VARCHAR(128) NOT NULL UNIQUE,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at),
            INDEX idx_session_id (session_id),
            INDEX idx_last_activity (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private function getAuditLogTableSQL() {
        return "CREATE TABLE IF NOT EXISTS audit_log (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at),
            INDEX idx_ip_address (ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    private function insertDefaultData() {
       
        // Set timezone for Indian Standard Time
        $this->conn->query("SET time_zone = '+05:30'");
        
        // Enable strict mode for better SQL validation
        $this->conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        
        // Set better transaction isolation level
        $this->conn->query("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
        
        // Optimize session settings
        $this->conn->query("SET SESSION wait_timeout = 28800");
        $this->conn->query("SET SESSION interactive_timeout = 28800");
    }

    public function getConnection() {
        // Check if connection is still alive
        if (!$this->conn || !$this->conn->ping()) {
            $this->initializeDatabase();
        }
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Initialize database
$database = new DatabaseConfig();
$conn = $database->getConnection();

// Function to log activities securely
function log_activity($conn, $user_id, $action, $description) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);
    
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Function to check database health
function check_database_health($conn) {
    try {
        $result = $conn->query("SELECT 1");
        return $result !== false;
    } catch (Exception $e) {
        error_log("Database health check failed: " . $e->getMessage());
        return false;
    }
}

// Success message (only show in development)
if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) {
    error_log("✅ Database initialized successfully for wallet_app");
}

// Register shutdown function to close connection
register_shutdown_function(function() use ($database) {
    $database->closeConnection();
});

?>