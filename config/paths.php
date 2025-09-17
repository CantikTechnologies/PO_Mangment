<?php
/**
 * Simplified Path Configuration for Hosting
 * This version works directly on hosting without complex path detection
 */

// Simple path detection for hosting
function getSimpleBasePath() {
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    // Check if we're on production server
    if (strpos($server_name, 'cantiktech.com') !== false || strpos($server_name, 'revenue-track') !== false) {
        // Production server - no relative base path needed
        return '';
    }
    
    // For localhost - use relative paths
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $script_dir = dirname($script_name);
    $depth = substr_count($script_dir, '/');
    
    // If we're in root directory
    if ($depth <= 1) {
        return '';
    }
    
    // Build relative path based on depth
    return str_repeat('../', $depth - 1);
}

// Set simple constants
$base_path = getSimpleBasePath();
define('BASE_PATH', $base_path);
define('CONFIG_PATH', $base_path . 'config');
define('ASSETS_PATH', $base_path . 'assets');
define('SRC_PATH', $base_path . 'src');
define('SHARED_PATH', $base_path . 'src/shared');
define('PATHS_LOADED', true);

// Simple helper functions
function getLoginUrl() {
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    // Check if we're on production server
    if (strpos($server_name, 'cantiktech.com') !== false || strpos($server_name, 'revenue-track') !== false) {
        // Production server - use absolute URL path
        return '/login.php';
    }
    
    // For localhost - use relative paths
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $script_dir = dirname($script_name);
    $depth = substr_count($script_dir, '/');
    
    if ($depth <= 1) {
        return 'login.php';
    } else {
        return str_repeat('../', $depth - 1) . 'login.php';
    }
}

function getSharedIncludePath($file) {
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    // Check if we're on production server
    if (strpos($server_name, 'cantiktech.com') !== false || strpos($server_name, 'revenue-track') !== false) {
        // Production server - use absolute path from document root
        return $_SERVER['DOCUMENT_ROOT'] . '/src/shared/' . $file;
    }
    
    // For localhost - use relative paths
    $script_dir = dirname($script_name);
    $depth = substr_count($script_dir, '/');
    
    if ($depth <= 1) {
        return 'src/shared/' . $file;
    } else {
        return str_repeat('../', $depth - 1) . 'src/shared/' . $file;
    }
}

function getConfigIncludePath($file) {
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    // Check if we're on production server
    if (strpos($server_name, 'cantiktech.com') !== false || strpos($server_name, 'revenue-track') !== false) {
        // Production server - use absolute path from document root
        return $_SERVER['DOCUMENT_ROOT'] . '/config/' . $file;
    }
    
    // For localhost - use relative paths
    $script_dir = dirname($script_name);
    $depth = substr_count($script_dir, '/');
    
    if ($depth <= 1) {
        return 'config/' . $file;
    } else {
        return str_repeat('../', $depth - 1) . 'config/' . $file;
    }
}
?>