<?php





// database info
$host = "localhost";
$db_name = "tangier_blog";
$username = "root";
$password = "";

try {
    // connect database
    $conn = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password
    );

    // show errors
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // log error internally, never expose details to user
    error_log("Database connection error: " . $e->getMessage());
    echo "An unexpected error occurred. Please try again later.";
}
