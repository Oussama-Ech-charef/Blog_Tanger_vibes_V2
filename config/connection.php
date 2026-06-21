<?php

// Load .env file for database credentials
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    foreach (explode("\n", $envContent) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'tangier_blog';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

require_once __DIR__ . '/constants.php';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    die("An unexpected error occurred. Please try again later.");
}
