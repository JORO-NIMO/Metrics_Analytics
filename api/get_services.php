<?php
// get_services.php
// Returns services ordered by id (nominal scale - no inherent order).
// Increments the service_views ratio-scale metric counter.
// Each service entity is defined as: {id, service_name, description, service_category, view_count}

include "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Direct measurement: increment global service_views counter (ratio scale)
$conn->query(
    "UPDATE platform_metrics SET metric_value = metric_value + 1,
     recorded_at = NOW() WHERE metric_name = 'service_views'"
);

$result = $conn->query("SELECT id, service_name, description, service_category, view_count FROM services ORDER BY id ASC");

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed", "status" => "error"]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // Cast ratio-scale integer from string (MySQL returns strings)
    $row['view_count'] = (int) $row['view_count'];
    $data[] = $row;
}

echo json_encode($data);
?>