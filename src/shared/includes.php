<?php
/**
 * Simple Universal Includes for Hosting
 * This version works directly on hosting
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Include simple paths
if (!defined('PATHS_LOADED')) {
    include_once __DIR__ . '/../../config/paths.php';
}

// Include database
if (!isset($conn)) {
    $db_paths = [
        __DIR__ . '/../../config/db.php',
        __DIR__ . '/../../../config/db.php',
        dirname(__DIR__, 2) . '/config/db.php',
    ];
    
    $db_loaded = false;
    foreach ($db_paths as $path) {
        if (file_exists($path)) {
            include_once $path;
            $db_loaded = true;
            break;
        }
    }
    
    if (!$db_loaded) {
        throw new Exception("Database configuration not found");
    }
}

// Include auth
if (!isset($auth)) {
    $auth_paths = [
        __DIR__ . '/../../config/auth.php',
        __DIR__ . '/../../../config/auth.php',
        dirname(__DIR__, 2) . '/config/auth.php',
    ];
    
    $auth_loaded = false;
    foreach ($auth_paths as $path) {
        if (file_exists($path)) {
            include_once $path;
            $auth_loaded = true;
            break;
        }
    }
    
    if (!$auth_loaded) {
        throw new Exception("Authentication system not found");
    }
}
?>