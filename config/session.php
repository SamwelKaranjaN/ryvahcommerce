<?php
class SessionHandler {
    private $sessionLifetime = 3600; // 1 hour
    private $regenerateTime = 300; // 5 minutes
    private $lastActivityTime;

    public function __construct() {
        $this->configureSession();
        $this->startSession();
        $this->checkSessionTimeout();
        $this->regenerateSession();
    }

    private function configureSession() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', $this->sessionLifetime);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
    }

    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->lastActivityTime = $_SESSION['last_activity'] ?? time();
    }

    private function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $this->sessionLifetime)) {
            $this->destroySession();
            header('Location: ' . SITE_URL . '/pages/login.php?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    private function regenerateSession() {
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > $this->regenerateTime) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

    public function destroySession() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
    }

    public function setFlashMessage($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}

// Initialize session handler
$sessionHandler = new SessionHandler(); 