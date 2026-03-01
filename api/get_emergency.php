<?php
// get_emergency.php
// Returns emergency records ordered by severity_score DESC (ratio scale),
// so highest-risk items surface first — a meaningful ordinal ordering.
//
// Dual-scale output:
//   severity       -> ordinal label  (Low / Medium / High)
//   severity_score -> ratio integer  (1-10)
// This respects the measurement theory principle that the formal
// relational system must preserve the empirical ordering relation.

include "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Direct measurement: increment emergency_lookups counter (ratio scale)
$conn->query(
    "UPDATE platform_metrics SET metric_value = metric_value + 1,
     recorded_at = NOW() WHERE metric_name = 'emergency_lookups'"
);

// Order by ratio-scale severity_score DESC so highest urgency appears first.
// This is a valid operation on a ratio scale (unlike ordinal alone).
$result = $conn->query(
    "SELECT id, title, short_description, detailed_description,
            severity, severity_score, advice
     FROM emergency_info
     ORDER BY severity_score DESC"
);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed", "status" => "error"]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['severity_score'] = (int) $row['severity_score'];
    $data[] = $row;
}

echo json_encode($data);
?>