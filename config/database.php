<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '@X6js1488');
define('DB_NAME', 'ryvahcommerce');

// Global database connection variable
global $conn;

// Create the single database connection
function initializeDatabase()
{
    global $conn;

    // Only create connection if it doesn't exist
    if (!isset($conn) || !$conn) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            die("Connection failed: " . $conn->connect_error);
        }

        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");

        // Set MySQL timezone to UTC
        $conn->query("SET time_zone = '+00:00'");

        // Enable error reporting for development
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }

    return $conn;
}

function getDBConnection()
{
    global $conn;

    if (!isset($conn) || !$conn) {
        return initializeDatabase();
    }

    // Check if connection is still alive
    if (!$conn->ping()) {
        $conn = null;
        return initializeDatabase();
    }

    return $conn;
}

// Close database connection
function closeDBConnection($connection = null)
{
    global $conn;

    $connectionToClose = $connection ?? $conn;

    if ($connectionToClose && $connectionToClose instanceof mysqli) {
        $connectionToClose->close();

        // If closing the global connection, reset it
        if ($connectionToClose === $conn) {
            $conn = null;
        }
    }
}

// Database Transaction Helper Class
class DatabaseTransaction
{
    private $conn;
    private $inTransaction = false;

    public function __construct($connection = null)
    {
        global $conn;
        $this->conn = $connection ?? $conn ?? getDBConnection();
    }

    public function begin()
    {
        if (!$this->inTransaction) {
            $this->conn->begin_transaction();
            $this->inTransaction = true;
        }
        return $this;
    }

    public function commit()
    {
        if ($this->inTransaction) {
            $this->conn->commit();
            $this->inTransaction = false;
        }
        return $this;
    }

    public function rollback()
    {
        if ($this->inTransaction) {
            $this->conn->rollback();
            $this->inTransaction = false;
        }
        return $this;
    }

    public function isInTransaction()
    {
        return $this->inTransaction;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}

// Helper function to execute prepared statements safely
function executeQuery($sql, $params = [], $types = '')
{
    global $conn;

    if (!$conn) {
        $conn = getDBConnection();
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    if (!empty($params)) {
        if (empty($types)) {
            // Auto-detect types
            $types = str_repeat('s', count($params));
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types = substr_replace($types, 'i', key($params), 1);
                } elseif (is_float($param)) {
                    $types = substr_replace($types, 'd', key($params), 1);
                }
                next($params);
            }
            reset($params);
        }

        $stmt->bind_param($types, ...$params);
    }

    $success = $stmt->execute();
    if (!$success) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    return $stmt;
}

// Helper function to fetch single row
function fetchRow($sql, $params = [], $types = '')
{
    $stmt = executeQuery($sql, $params, $types);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Helper function to fetch all rows
function fetchAll($sql, $params = [], $types = '')
{
    $stmt = executeQuery($sql, $params, $types);
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Initialize the database connection when this file is included
initializeDatabase();

// Cleanup function for script termination
register_shutdown_function(function () {
    global $conn;
    if ($conn) {
        closeDBConnection();
    }
});