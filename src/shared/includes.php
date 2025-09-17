<?php
/**
 * Universal Include File for PO Management System
 * This file handles all common includes and path resolution
 */

// Start session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Include path configuration
if (!defined('PATHS_LOADED')) {
    // Try different possible paths for the config directory
    $config_paths = [
        __DIR__ . '/../../config/paths.php',  // From src/shared/
        __DIR__ . '/../../../config/paths.php', // From src/Modules/ModuleName/
        dirname(__DIR__, 2) . '/config/paths.php', // Relative to project root
    ];
    
    $paths_loaded = false;
    foreach ($config_paths as $path) {
        if (file_exists($path)) {
            include_once $path;
            $paths_loaded = true;
            break;
        }
    }
    
    if (!$paths_loaded) {
        // Fallback: define basic constants
        define('BASE_PATH', '');
        define('CONFIG_PATH', '/config');
        define('ASSETS_PATH', '/assets');
        define('SRC_PATH', '/src');
        define('SHARED_PATH', '/src/shared');
        define('PATHS_LOADED', true);
    }
}

// Include database connection
if (!isset($conn)) {
    $db_paths = [
        __DIR__ . '/../../config/db.php',  // From src/shared/
        __DIR__ . '/../../../config/db.php', // From src/Modules/ModuleName/
        dirname(__DIR__, 2) . '/config/db.php', // Relative to project root
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

// Include authentication system
if (!isset($auth)) {
    $auth_paths = [
        __DIR__ . '/../../config/auth.php',  // From src/shared/
        __DIR__ . '/../../../config/auth.php', // From src/Modules/ModuleName/
        dirname(__DIR__, 2) . '/config/auth.php', // Relative to project root
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

// Helper function to get the correct login redirect URL
function getLoginUrl() {
    // Try to determine the correct path to login.php
    $login_paths = [
        '/login.php',
        '/../login.php',
        '/../../login.php',
        '/../../../login.php',
    ];
    
    // Use the most appropriate path based on current location
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    $depth = substr_count($current_dir, '/');
    
    if ($depth >= 3) {
        return '/../../../login.php';
    } elseif ($depth >= 2) {
        return '/../../login.php';
    } elseif ($depth >= 1) {
        return '/../login.php';
    } else {
        return '/login.php';
    }
}

// Helper function to get the correct path to shared files
function getSharedIncludePath($file) {
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    $depth = substr_count($current_dir, '/');
    
    if ($depth >= 3) {
        return '../../shared/' . $file;
    } elseif ($depth >= 2) {
        return '../shared/' . $file;
    } else {
        return 'src/shared/' . $file;
    }
}

// Helper function to get the correct path to config files
function getConfigIncludePath($file) {
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    $depth = substr_count($current_dir, '/');
    
    if ($depth >= 3) {
        return '../../../config/' . $file;
    } elseif ($depth >= 2) {
        return '../../config/' . $file;
    } else {
        return 'config/' . $file;
    }
}
?>
