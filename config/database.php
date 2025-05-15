<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '@X6js1488');
define('DB_NAME', 'ryvahcommerce');

// Create database connection
function getDBConnection() {
    $conn = mysqli_connect(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME
    );

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Set charset to utf8mb4
    mysqli_set_charset($conn, "utf8mb4");
    
    return $conn;
}

// Close database connection
function closeDBConnection($conn) {
    mysqli_close($conn);
}

class DatabaseTransaction {
    private $conn;
    private $inTransaction = false;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function begin() {
        if (!$this->inTransaction) {
            $this->conn->begin_transaction();
            $this->inTransaction = true;
        }
    }

    public function commit() {
        if ($this->inTransaction) {
            $this->conn->commit();
            $this->inTransaction = false;
        }
    }

    public function rollback() {
        if ($this->inTransaction) {
            $this->conn->rollback();
            $this->inTransaction = false;
        }
    }

    public function isInTransaction() {
        return $this->inTransaction;
    }
}
?> 