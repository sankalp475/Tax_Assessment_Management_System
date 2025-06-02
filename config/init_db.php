<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = 'root@475';
$dbname = 'tax_assessment_db';

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

// Set up logging
$logFile = $logDir . '/database.log';
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    // Create connection without database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop database if exists and create new one
    $pdo->exec("DROP DATABASE IF EXISTS $dbname");
    $pdo->exec("CREATE DATABASE $dbname");
    writeLog("Database created successfully");

    // Select the database
    $pdo->exec("USE $dbname");

    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/../database/init.sql');
    
    // Split SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Log the error but continue with other statements
                writeLog("Error executing statement: " . $e->getMessage());
            }
        }
    }
    
    writeLog("Database tables created and populated successfully");

} catch(PDOException $e) {
    writeLog("Error: " . $e->getMessage());
    die();
}
?> 
