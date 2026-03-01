<?php
// backend/connection.php  — Run this to test your DB connection
require_once 'config.php';

echo "<h2>🔌 Testing Database Connection</h2>";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color:green'>✅ Connected to <strong>maternal_health_uganda</strong> successfully!</p>";

    // Show tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>📊 Tables found: <strong>" . implode(', ', $tables) . "</strong></p>";

    // Show user count
    $count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "<p>👤 Users in database: <strong>$count</strong></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ " . $e->getMessage() . "</p>";
}
?>
