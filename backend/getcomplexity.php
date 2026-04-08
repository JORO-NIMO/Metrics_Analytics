<?php
// getcomplexity.php
// Lecture 06: Structural Complexity
// Returns cyclomatic complexity, cohesion, coupling, information flow,
// architecture morphology, and data structure complexity

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // ── Cyclomatic Complexity ─────────────────────────────────────────────────
    $ccRows = $pdo->query("
        SELECT file_name, decision_points, cyclomatic_v, risk_level
        FROM   cyclomatic_complexity
        ORDER  BY cyclomatic_v DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $avgV     = count($ccRows) > 0
        ? round(array_sum(array_column($ccRows, 'cyclomatic_v')) / count($ccRows), 2)
        : 0;
    $highRisk = array_values(array_filter($ccRows,
        fn($r) => in_array($r['risk_level'], ['high', 'very_high'])
    ));

    // ── Cohesion ──────────────────────────────────────────────────────────────
    $cohRows = $pdo->query("
        SELECT module_name, module_description, internal_relations,
               external_relations, cohesion_pct, cohesion_type
        FROM   cohesion_metrics
        ORDER  BY cohesion_pct DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $sysCohesion = count($cohRows) > 0
        ? round(array_sum(array_column($cohRows, 'cohesion_pct')) / count($cohRows), 2)
        : 0;

    // ── Coupling ──────────────────────────────────────────────────────────────
    $cpRows = $pdo->query("
        SELECT module_x, module_y, coupling_type, coupling_rank,
               interconnections, coupling_value
        FROM   coupling_metrics
        ORDER  BY coupling_value ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Global coupling = median of all coupling values
    $vals = array_column($cpRows, 'coupling_value');
    sort($vals);
    $n = count($vals);
    $globalCoupling = $n === 0 ? 0
        : ($n % 2 === 1
            ? $vals[intdiv($n, 2)]
            : round(($vals[$n/2 - 1] + $vals[$n/2]) / 2, 4));

    // ── Information Flow ──────────────────────────────────────────────────────
    $flowRows = $pdo->query("
        SELECT module_name, fan_in, fan_out, ifc_value
        FROM   information_flow
        ORDER  BY ifc_value DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ── Architecture ──────────────────────────────────────────────────────────
    $arch = $pdo->query("
        SELECT system_name, nodes, edges, arch_depth, arch_width,
               edge_node_ratio, impurity
        FROM   architecture_metrics
        LIMIT  1
    ")->fetch(PDO::FETCH_ASSOC);

    // ── Data Structure Complexity ─────────────────────────────────────────────
    $dscRows = $pdo->query("
        SELECT file_name, integer_vars, string_vars, array_vars,
               c1_integers, c2_strings, c3_arrays, total_complexity
        FROM   data_structure_complexity
        ORDER  BY total_complexity DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'cyclomatic' => [
            'files'      => $ccRows,
            'avg_v'      => $avgV,
            'high_risk'  => $highRisk,
            'file_count' => count($ccRows),
        ],
        'cohesion' => [
            'modules'         => $cohRows,
            'system_cohesion' => $sysCohesion,
        ],
        'coupling' => [
            'pairs'           => $cpRows,
            'global_coupling' => $globalCoupling,
        ],
        'information_flow' => [
            'modules' => $flowRows,
        ],
        'architecture' => $arch ?: (object)[],
        'data_structure' => [
            'files' => $dscRows,
        ],
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
