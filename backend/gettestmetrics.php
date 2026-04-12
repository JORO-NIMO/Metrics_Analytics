<?php
/**
 * backend/gettestmetrics.php
 * Lecture 10: Software Test Metrics API
 * Returns test metrics data for the dashboard Test Metrics tab
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
    
    // Get test coverage by file
    $stmt = $db->prepare("
        SELECT * FROM test_coverage 
        WHERE release_version = (SELECT MAX(release_version) FROM test_coverage)
        ORDER BY target_file ASC
    ");
    $stmt->execute();
    $coverage_by_file = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate overall coverage metrics
    $stmt = $db->prepare("
        SELECT 
            SUM(total_statements) as total_stmts,
            SUM(tested_statements) as tested_stmts,
            SUM(total_branches) as total_branches,
            SUM(tested_branches) as tested_branches,
            SUM(total_gui_elements) as total_gui,
            SUM(tested_gui_elements) as tested_gui
        FROM test_coverage 
        WHERE release_version = (SELECT MAX(release_version) FROM test_coverage)
    ");
    $stmt->execute();
    $coverage_totals = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate coverage percentages
    $cvs = ($coverage_totals['total_stmts'] > 0) ? 
           round(($coverage_totals['tested_stmts'] / $coverage_totals['total_stmts']) * 100, 2) : 0;
    $cvb = ($coverage_totals['total_branches'] > 0) ? 
           round(($coverage_totals['tested_branches'] / $coverage_totals['total_branches']) * 100, 2) : 0;
    $cvgui = ($coverage_totals['total_gui'] > 0) ? 
             round(($coverage_totals['tested_gui'] / $coverage_totals['total_gui']) * 100, 2) : 0;
    
    // Get test execution results
    $stmt = $db->prepare("
        SELECT result, COUNT(*) as count
        FROM test_executions 
        WHERE release_version = (SELECT MAX(release_version) FROM test_executions)
        GROUP BY result
    ");
    $stmt->execute();
    $execution_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate pass/fail/pending rates
    $total_executions = array_sum(array_column($execution_results, 'count'));
    $passed = array_column($execution_results, 'count', 'result')['pass'] ?? 0;
    $failed = array_column($execution_results, 'count', 'result')['fail'] ?? 0;
    $pending = array_column($execution_results, 'count', 'result')['pending'] ?? 0;
    
    $rtp = ($total_executions > 0) ? round(($passed / $total_executions) * 100, 2) : 0;
    $rtf = ($total_executions > 0) ? round(($failed / $total_executions) * 100, 2) : 0;
    $rtpend = ($total_executions > 0) ? round(($pending / $total_executions) * 100, 2) : 0;
    
    // Get test cases with latest execution results
    $stmt = $db->prepare("
        SELECT tc.*, 
               te.result as latest_result,
               te.execution_time_ms,
               te.executed_at
        FROM test_cases tc
        LEFT JOIN test_executions te ON tc.test_id = te.test_id
        WHERE te.release_version = (SELECT MAX(release_version) FROM test_executions)
           OR te.release_version IS NULL
        ORDER BY tc.is_critical DESC, tc.test_name ASC
    ");
    $stmt->execute();
    $test_cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get defect estimation data
    $stmt = $db->prepare("
        SELECT * FROM defect_estimation 
        WHERE release_version = (SELECT MAX(release_version) FROM defect_estimation)
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute();
    $defect_estimation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get phase containment effectiveness
    $stmt = $db->prepare("
        SELECT * FROM phase_containment 
        WHERE release_version = (SELECT MAX(release_version) FROM phase_containment)
        ORDER BY phase ASC
    ");
    $stmt->execute();
    $phase_containment = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate test estimation
    $budget_ugx = 5727600; // UGX 5.7M budget
    $cost_per_case = 92500; // UGX per test case
    $time_weeks = 16;
    $staff_count = 7;
    $hours_per_week = 40;
    $utilization_factor = 0.10; // 10% of time for testing
    $hours_per_case = 4;
    
    $n1 = intdiv($budget_ugx * $utilization_factor, $cost_per_case);
    $n2 = intdiv($time_weeks * $staff_count * $hours_per_week * $utilization_factor, $hours_per_case);
    $n_recommended = min($n1, $n2);
    $n_implemented = count($test_cases);
    
    // Calculate component coverage (PHP files vs HTML files)
    $php_files = array_filter($coverage_by_file, function($file) {
        return strpos($file['target_file'], '.php') !== false;
    });
    $html_files = array_filter($coverage_by_file, function($file) {
        return strpos($file['target_file'], '.html') !== false;
    });
    
    $component_coverage = count($coverage_by_file) > 0 ? 
                         round((count($coverage_by_file) / 15) * 100, 2) : 0; // 15 total components
    
    // Prepare response data
    $response = [
        'success' => true,
        'data' => [
            'summary_cards' => [
                'statement_coverage' => $cvs,
                'branch_coverage' => $cvb,
                'component_coverage' => $component_coverage,
                'gui_coverage' => $cvgui,
                'test_pass_rate' => $rtp,
                'test_fail_rate' => $rtf,
                'remaining_defects' => $defect_estimation['nr_comparative'] ?? 0,
                'undetected_defects' => $defect_estimation['nd_comparative'] ?? 0
            ],
            'coverage_table' => $coverage_by_file,
            'test_cases_table' => $test_cases,
            'defect_estimation' => $defect_estimation,
            'phase_containment' => $phase_containment,
            'test_estimation' => [
                'budget_ugx' => $budget_ugx,
                'cost_per_case' => $cost_per_case,
                'n1_from_budget' => $n1,
                'time_weeks' => $time_weeks,
                'staff_count' => $staff_count,
                'hours_per_case' => $hours_per_case,
                'n2_from_time' => $n2,
                'n_recommended' => $n_recommended,
                'n_implemented' => $n_implemented,
                'implementation_status' => $n_implemented >= $n_recommended ? 'Adequate' : 'Insufficient'
            ],
            'execution_summary' => [
                'total_executions' => $total_executions,
                'passed' => $passed,
                'failed' => $failed,
                'pending' => $pending,
                'pass_rate' => $rtp,
                'fail_rate' => $rtf,
                'pending_rate' => $rtpend
            ],
            'coverage_summary' => [
                'total_statements' => $coverage_totals['total_stmts'],
                'tested_statements' => $coverage_totals['tested_stmts'],
                'total_branches' => $coverage_totals['total_branches'],
                'tested_branches' => $coverage_totals['tested_branches'],
                'total_gui_elements' => $coverage_totals['total_gui'],
                'tested_gui_elements' => $coverage_totals['tested_gui'],
                'cvs_percentage' => $cvs,
                'cvb_percentage' => $cvb,
                'cvgui_percentage' => $cvgui
            ],
            'test_types' => [
                'feature' => count(array_filter($test_cases, function($tc) { return $tc['test_type'] === 'feature'; })),
                'load' => count(array_filter($test_cases, function($tc) { return $tc['test_type'] === 'load'; })),
                'regression' => count(array_filter($test_cases, function($tc) { return $tc['test_type'] === 'regression'; })),
                'certification' => count(array_filter($test_cases, function($tc) { return $tc['test_type'] === 'certification'; }))
            ],
            'critical_tests' => [
                'total_critical' => count(array_filter($test_cases, function($tc) { return $tc['is_critical']; })),
                'critical_passed' => count(array_filter($test_cases, function($tc) { 
                    return $tc['is_critical'] && $tc['latest_result'] === 'pass'; 
                })),
                'critical_coverage' => count(array_filter($test_cases, function($tc) { return $tc['is_critical']; })) > 0 ?
                                      round((count(array_filter($test_cases, function($tc) { 
                                          return $tc['is_critical'] && $tc['latest_result'] === 'pass'; 
                                      })) / count(array_filter($test_cases, function($tc) { return $tc['is_critical']; }))) * 100, 2) : 0
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
