<?php
/**
 * backend/session_config.php
 * Session configuration - must be included before session_start()
 */

// Session configuration for security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.gc_maxlifetime', 3600); // 1 hour timeout

// Start session
session_start();
?>
