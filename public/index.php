<?php
// Start output buffering at the very beginning
ob_start();

// Enable error reporting in development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Debug output
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../routes/web.php';

// All routing and error handling is now managed in web.php
// ob_end_flush();
