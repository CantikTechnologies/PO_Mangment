<?php
/**
 * Path Configuration for PO Management System
 * Handles dynamic path resolution for both local and online deployment
 */

// Get the document root and script directory
$document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// Determine if we're running locally or online
$is_local = (
    strpos($document_root, 'xampp') !== false || 
    strpos($document_root, 'htdocs') !== false ||
    strpos($document_root, 'localhost') !== false ||
    strpos($document_root, '127.0.0.1') !== false ||
    (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
);

// Calculate the base path dynamically
function getBasePath() {
    global $script_name, $request_uri;
    
    // Parse the script path to determine the project root
    $script_parts = explode('/', trim($script_name, '/'));
    $request_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
    
    // Find the project root by looking for common project files
    $project_root = '';
    for ($i = 0; $i < count($script_parts); $i++) {
        $test_path = '/' . implode('/', array_slice($script_parts, 0, $i + 1));
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $test_path . '/index.php') || 
            file_exists($_SERVER['DOCUMENT_ROOT'] . $test_path . '/login.php')) {
            $project_root = $test_path;
            break;
        }
    }
    
    // Fallback: use the directory containing the current script
    if (empty($project_root)) {
        $project_root = dirname($script_name);
        if ($project_root === '/') $project_root = '';
    }
    
    return $project_root;
}

// Set global path constants
define('BASE_PATH', getBasePath());
define('CONFIG_PATH', BASE_PATH . '/config');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('SRC_PATH', BASE_PATH . '/src');
define('SHARED_PATH', SRC_PATH . '/shared');

// Helper functions for path resolution
function getConfigPath($file = '') {
    return CONFIG_PATH . ($file ? '/' . ltrim($file, '/') : '');
}

function getAssetsPath($file = '') {
    return ASSETS_PATH . ($file ? '/' . ltrim($file, '/') : '');
}

function getSrcPath($file = '') {
    return SRC_PATH . ($file ? '/' . ltrim($file, '/') : '');
}

function getSharedPath($file = '') {
    return SHARED_PATH . ($file ? '/' . ltrim($file, '/') : '');
}

// Include path helper
function includeConfig($file) {
    $path = getConfigPath($file);
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        include_once $_SERVER['DOCUMENT_ROOT'] . $path;
    } else {
        // Fallback to relative path
        $relative_path = '../../../config/' . $file;
        if (file_exists($relative_path)) {
            include_once $relative_path;
        } else {
            throw new Exception("Config file not found: $file");
        }
    }
}

function includeShared($file) {
    $path = getSharedPath($file);
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        include_once $_SERVER['DOCUMENT_ROOT'] . $path;
    } else {
        // Fallback to relative path
        $relative_path = '../../shared/' . $file;
        if (file_exists($relative_path)) {
            include_once $relative_path;
        } else {
            throw new Exception("Shared file not found: $file");
        }
    }
}

// URL helper for generating correct URLs
function getBaseUrl() {
    global $is_local;
    
    if ($is_local) {
        // For local development
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . BASE_PATH;
    } else {
        // For production - use relative paths
        return BASE_PATH;
    }
}

function getAssetUrl($file) {
    return getBaseUrl() . '/assets/' . ltrim($file, '/');
}

function getModuleUrl($module, $file = '') {
    return getBaseUrl() . '/src/Modules/' . $module . ($file ? '/' . ltrim($file, '/') : '');
}

// Debug function (remove in production)
function debugPaths() {
    if (isset($_GET['debug_paths'])) {
        echo "<pre>";
        echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
        echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
        echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
        echo "Base Path: " . BASE_PATH . "\n";
        echo "Config Path: " . CONFIG_PATH . "\n";
        echo "Assets Path: " . ASSETS_PATH . "\n";
        echo "Is Local: " . ($is_local ? 'Yes' : 'No') . "\n";
        echo "</pre>";
    }
}

// Auto-include this file when needed
if (!defined('PATHS_LOADED')) {
    define('PATHS_LOADED', true);
}
?>
