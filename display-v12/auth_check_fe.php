<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Determine the protocol (http or https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    
    // Get the current host
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the path to the root (assuming index.php is in the same directory or adjust as needed)
    // We want to redirect to display-v12/index.php
    // Since this file is included in files within display-v12, relative path 'index.php' should work usually.
    // However, to be safe and handle different inclusion contexts, we can use a relative redirect.
    
    header("Location: index.php");
    exit;
}
?>
