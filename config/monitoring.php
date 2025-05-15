<?php
class MonitoringSystem {
    private $logFile;
    private $alertEmail;
    private $errorThreshold = 5; // Number of errors before alert
    private $errorWindow = 300; // 5 minutes
    private $errors = [];

    public function __construct($alertEmail) {
        $this->logFile = __DIR__ . '/../logs/monitoring.log';
        $this->alertEmail = $alertEmail;
        
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }

    public function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Add to error tracking
        $this->errors[] = $logEntry;
        
        // Clean old errors
        $this->cleanOldErrors();
        
        // Check if we need to send an alert
        if ($this->shouldSendAlert()) {
            $this->sendAlert();
        }
        
        // Log to file
        file_put_contents(
            $this->logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND
        );
    }

    public function logPerformance($action, $duration) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'type' => 'performance',
            'action' => $action,
            'duration' => $duration,
            'memory_usage' => memory_get_usage(true)
        ];
        
        file_put_contents(
            $this->logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND
        );
    }

    private function cleanOldErrors() {
        $now = time();
        $this->errors = array_filter($this->errors, function($error) use ($now) {
            return strtotime($error['timestamp']) > ($now - $this->errorWindow);
        });
    }

    private function shouldSendAlert() {
        return count($this->errors) >= $this->errorThreshold;
    }

    private function sendAlert() {
        $subject = "ALERT: Multiple errors detected on " . $_SERVER['HTTP_HOST'];
        $message = "Multiple errors have been detected in the last " . ($this->errorWindow / 60) . " minutes:\n\n";
        
        foreach ($this->errors as $error) {
            $message .= "Time: {$error['timestamp']}\n";
            $message .= "Message: {$error['message']}\n";
            $message .= "IP: {$error['ip']}\n";
            $message .= "User Agent: {$error['user_agent']}\n";
            $message .= "Context: " . json_encode($error['context']) . "\n\n";
        }
        
        mail($this->alertEmail, $subject, $message);
        
        // Clear errors after sending alert
        $this->errors = [];
    }

    public function getSystemStatus() {
        $status = [
            'memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'disk_free_space' => disk_free_space('/'),
            'disk_total_space' => disk_total_space('/'),
            'load_average' => sys_getloadavg(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
        ];
        
        return $status;
    }

    public function checkDatabaseConnection($conn) {
        try {
            $conn->ping();
            return true;
        } catch (Exception $e) {
            $this->logError("Database connection failed", ['error' => $e->getMessage()]);
            return false;
        }
    }
}

// Initialize monitoring system
 