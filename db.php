
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "university_management_system";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed. Please check your XAMPP MySQL server and database name.");
}

?>
