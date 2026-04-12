<?php
/**
 * backend/getreliabilitymetrics.php
 * Lecture 09: Software Reliability Metrics API
 * Returns reliability data for the dashboard Reliability tab
 */
require_once 'session_config.php';
require_once 'config.php';
require_once 'metrics_logger.php';

header('Content-Type: application/json');

// Admin access check
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get latest reliability metrics
    $stmt = $db->prepare("
        SELECT * FROM reliability_metrics 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get interfailure times for trend analysis
    $stmt = $db->prepare("
        SELECT * FROM interfailure_times 
        ORDER BY failure_sequence ASC
    ");
    $stmt->execute();
    $interfailure_times = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get system errors grouped by endpoint and severity
    $stmt = $db->prepare("
        SELECT endpoint, severity, COUNT(*) as error_count,
               MIN(created_at) as first_occurrence,
               MAX(created_at) as last_occurrence
        FROM system_errors 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY endpoint, severity
        ORDER BY error_count DESC
    ");
    $stmt->execute();
    $system_errors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get failure log for reliability analysis
    $stmt = $db->prepare("
        SELECT * FROM reliability_failures 
        WHERE failure_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY failure_time DESC
        LIMIT 50
    ");
    $stmt->execute();
    $failure_log = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate computed metrics
    $total_failures = $metrics['total_failures'] ?? 0;
    $total_time = $metrics['total_time'] ?? 1;
    $lambda = $total_failures / $total_time; // failure intensity
    $mttf = ($lambda > 0) ? 1 / $lambda : null;
    $mttr = $metrics['mttr'] ?? 6.0; // minutes
    $tm_transactions = 250; // average transactions per minute
    
    // Availability: A = 1/(1+lambda×tm)
    $availability = 1 / (1 + ($lambda * $tm_transactions));
    
    // Reliability: R(t) = e^(-lambda×t) for t=1M transactions
    $t = 1000000;
    $reliability = exp(-$lambda * $t);
    
    // Serial system reliability for 15 components
    $num_components = 15;
    $lambda_system = $lambda * $num_components;
    $serial_reliability = exp(-$lambda_system * $t);
    
    // Operational profile from page_views
    $stmt = $db->prepare("
        SELECT endpoint, COUNT(*) as usage_count,
               (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM page_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY))) as percentage
        FROM page_views 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY endpoint
        ORDER BY usage_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $operational_profile = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare response data
    $response = [
        'success' => true,
        'data' => [
            'summary_cards' => [
                'total_failures' => $total_failures,
                'failure_intensity' => number_format($lambda * 1000000, 8), // per million transactions
                'mttf' => $mttf ? number_format($mttf, 2) : 'N/A',
                'mttr' => number_format($mttr, 2),
                'availability' => number_format($availability, 8),
                'reliability' => number_format($reliability, 8),
                'laplace_factor' => number_format($metrics['laplace_factor'] ?? 0, 2),
                'objective_met' => (bool)($metrics['objective_met'] ?? 0)
            ],
            'availability_calculation' => [
                'formula' => 'A = 1/(1+lambda×tm)',
                'lambda' => number_format($lambda, 8),
                'tm_transactions' => $tm_transactions,
                'result' => number_format($availability, 8)
            ],
            'serial_reliability' => [
                'formula' => 'R_system = e^(-lambda_total × t)',
                'num_components' => $num_components,
                'lambda_system' => number_format($lambda_system, 8),
                't' => $t,
                'result' => number_format($serial_reliability, 8)
            ],
            'interfailure_times' => $interfailure_times,
            'failure_chain' => [
                ['sequence' => 1, 'type' => 'Error', 'example' => 'Invalid LMP date input validation error', 'observable' => true],
                ['sequence' => 2, 'type' => 'Fault', 'example' => 'Missing boundary check in date validation function', 'observable' => false],
                ['sequence' => 3, 'type' => 'Failure', 'example' => 'System crashes when processing invalid dates', 'observable' => true]
            ],
            'system_errors' => $system_errors,
            'failure_log' => $failure_log,
            'operational_profile' => $operational_profile,
            'trend_analysis' => [
                'laplace_factor' => $metrics['laplace_factor'] ?? 0,
                'trend_direction' => $metrics['trend_direction'] ?? 'stable',
                'interpretation' => ($metrics['laplace_factor'] ?? 0) < -0.5 ? 'Reliability improving' : 
                                  (($metrics['laplace_factor'] ?? 0) > 0.5 ? 'Reliability declining' : 'Stable')
            ],
            'release_criteria' => [
                'current_lambda' => number_format($lambda, 8),
                'lambda_objective' => number_format($metrics['failure_intensity_objective'] ?? 0.001, 8),
                'ratio' => ($metrics['failure_intensity_objective'] ?? 0.001) > 0 ? 
                          number_format($lambda / ($metrics['failure_intensity_objective'] ?? 0.001), 3) : 0,
                'release_ready' => $lambda <= ($metrics['failure_intensity_objective'] ?? 0.001)
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
