Db · PHP
Copy

<?php
// db.php — Database connection
// Measurement principle: connection status is a nominal attribute
// (connected / failed). Error is reported as structured JSON.

$conn = new mysqli("localhost", "root", "", "maternal_health");

if ($conn->connect_error) {
    http_response_code(503);
    die(json_encode([
        "error"   => "Database connection failed",
        "detail"  => $conn->connect_error,
        "status"  => "error"
    ]));
}

$conn->set_charset("utf8mb4");
?>