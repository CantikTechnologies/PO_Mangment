<?php
/**
 * Simple Universal Includes for Hosting
 * This version works directly on hosting
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Include simple paths (optional)
if (!defined('PATHS_LOADED')) {
    include_once __DIR__ . '/../../config/paths.php';
}

// Include database
if (!isset($conn)) {
    $db_paths = [
        __DIR__ . '/config/db.php',
        __DIR__ . '/config/db.php',
        dirname(__DIR__, 2) . '/config/db.php',
    ];
    $db_loaded = false;
    foreach ($db_paths as $path) {
        if (file_exists($path)) { include_once $path; $db_loaded = true; break; }
    }
    // Fallback to document root
    if (!$db_loaded) {
        $doc = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        if ($doc && file_exists($doc . '/config/db.php')) { include_once $doc . '/config/db.php'; $db_loaded = true; }
    }
    if (!$db_loaded) {
        if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
        echo "Configuration error: config/db.php not found.\n";
        echo "Tried paths:\n";
        foreach ($db_paths as $p) { echo " - $p\n"; }
        if (!empty($doc)) { echo " - $doc/config/db.php\n"; }
        exit;
    }
}

// Include auth
if (!isset($auth)) {
    $auth_paths = [
        __DIR__ . '/config/auth.php',
        __DIR__ . '/config/auth.php',
        dirname(__DIR__, 2) . '/config/auth.php',
    ];
    $auth_loaded = false;
    foreach ($auth_paths as $path) {
        if (file_exists($path)) { include_once $path; $auth_loaded = true; break; }
    }
    if (!$auth_loaded) {
        $doc = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        if ($doc && file_exists($doc . '/config/auth.php')) { include_once $doc . '/config/auth.php'; $auth_loaded = true; }
    }
    if (!$auth_loaded) {
        if (!headers_sent()) { header('Content-Type: text/plain; charset=utf-8'); }
        echo "Configuration error: config/auth.php not found.\n";
        foreach ($auth_paths as $p) { echo " - $p\n"; }
        if (!empty($doc)) { echo " - $doc/config/auth.php\n"; }
        exit;
    }
}
?>