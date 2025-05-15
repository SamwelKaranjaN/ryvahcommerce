<?php
require_once __DIR__ . '../../config/settings.php';

// Destroy session
session_destroy();

// Redirect to login page
redirect('login.php');
?> 