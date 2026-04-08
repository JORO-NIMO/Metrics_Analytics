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
    $database = new Database();
    $pdo = $database->getConnection();

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
    
    // Enhanced cyclomatic calculations
    $lowRisk = array_values(array_filter($ccRows, fn($r) => $r['risk_level'] === 'low'));
    $moderateRisk = array_values(array_filter($ccRows, fn($r) => $r['risk_level'] === 'moderate'));
    $maxComplexity = !empty($ccRows) ? max(array_column($ccRows, 'cyclomatic_v')) : 0;
    $minComplexity = !empty($ccRows) ? min(array_column($ccRows, 'cyclomatic_v')) : 0;
    $complexityDistribution = [
        'low' => count($lowRisk),
        'moderate' => count($moderateRisk),
        'high' => count(array_values(array_filter($ccRows, fn($r) => $r['risk_level'] === 'high'))),
        'very_high' => count(array_values(array_filter($ccRows, fn($r) => $r['risk_level'] === 'very_high')))
    ];
    
    // Complexity quality indicators
    $complexityScore = $avgV <= 10 ? 'Excellent' : ($avgV <= 15 ? 'Good' : ($avgV <= 20 ? 'Fair' : 'Poor'));
    $maintainabilityEffort = $maxComplexity > 50 ? 'Very High' : ($maxComplexity > 20 ? 'High' : ($maxComplexity > 10 ? 'Medium' : 'Low'));
    $testingComplexity = count($highRisk) > 0 ? 'Complex' : 'Simple';

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
    
    // Enhanced cohesion calculations
    $functionalCohesion = array_values(array_filter($cohRows, fn($r) => $r['cohesion_type'] === 'functional'));
    $avgInternalRelations = !empty($cohRows) ? round(array_sum(array_column($cohRows, 'internal_relations')) / count($cohRows), 1) : 0;
    $avgExternalRelations = !empty($cohRows) ? round(array_sum(array_column($cohRows, 'external_relations')) / count($cohRows), 1) : 0;
    $cohesionBalance = abs($avgInternalRelations - $avgExternalRelations) / max($avgInternalRelations + $avgExternalRelations, 1) * 100;
    
    // Cohesion quality indicators
    $cohesionQuality = $sysCohesion >= 80 ? 'Excellent' : ($sysCohesion >= 60 ? 'Good' : ($sysCohesion >= 40 ? 'Fair' : 'Poor'));
    $modularDesign = count($functionalCohesion) / max(count($cohRows), 1) * 100; // % of functional cohesion
    $designPattern = $cohesionBalance <= 20 ? 'Well-Balanced' : ($cohesionBalance <= 40 ? 'Moderately Balanced' : 'Imbalanced');

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
    
    // Enhanced coupling calculations
    $avgCoupling = !empty($cpRows) ? round(array_sum(array_column($cpRows, 'coupling_value')) / count($cpRows), 4) : 0;
    $maxCoupling = !empty($cpRows) ? max(array_column($cpRows, 'coupling_value')) : 0;
    $avgInterconnections = !empty($cpRows) ? round(array_sum(array_column($cpRows, 'interconnections')) / count($cpRows), 1) : 0;
    
    // Coupling type distribution
    $couplingTypes = ['R0' => 0, 'R1' => 0, 'R2' => 0, 'R3' => 0, 'R4' => 0];
    foreach ($cpRows as $cp) {
        if (isset($couplingTypes[$cp['coupling_type']])) {
            $couplingTypes[$cp['coupling_type']]++;
        }
    }
    
    // Coupling quality indicators
    $couplingQuality = $globalCoupling <= 1.0 ? 'Excellent' : ($globalCoupling <= 2.0 ? 'Good' : ($globalCoupling <= 3.0 ? 'Fair' : 'Poor'));
    $moduleIndependence = $globalCoupling <= 1.5 ? 'High' : ($globalCoupling <= 2.5 ? 'Medium' : 'Low');
    $dominantCouplingType = array_keys($couplingTypes, max($couplingTypes))[0];

    // ── Information Flow ──────────────────────────────────────────────────────
    $flowRows = $pdo->query("
        SELECT module_name, fan_in, fan_out, ifc_value
        FROM   information_flow
        ORDER  BY ifc_value DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Enhanced information flow calculations
    $avgFanIn = !empty($flowRows) ? round(array_sum(array_column($flowRows, 'fan_in')) / count($flowRows), 1) : 0;
    $avgFanOut = !empty($flowRows) ? round(array_sum(array_column($flowRows, 'fan_out')) / count($flowRows), 1) : 0;
    $maxIFC = !empty($flowRows) ? max(array_column($flowRows, 'ifc_value')) : 0;
    $flowComplexity = $maxIFC > 1000 ? 'Very High' : ($maxIFC > 500 ? 'High' : ($maxIFC > 100 ? 'Medium' : 'Low'));
    
    // Information flow quality indicators
    $flowBalance = abs($avgFanIn - $avgFanOut) / max($avgFanIn + $avgFanOut, 1) * 100;
    $flowStability = $flowBalance <= 20 ? 'Stable' : ($flowBalance <= 40 ? 'Moderately Stable' : 'Unstable');
    $interfaceComplexity = $avgFanIn + $avgFanOut;

    // ── Architecture ──────────────────────────────────────────────────────────
    $arch = $pdo->query("
        SELECT system_name, nodes, edges, arch_depth, arch_width,
               edge_node_ratio, impurity
        FROM   architecture_metrics
        LIMIT  1
    ")->fetch(PDO::FETCH_ASSOC);

    // Enhanced architecture calculations
    $archComplexity = $arch ? ($arch['edges'] - $arch['nodes'] + 1) : 0;
    $density = $arch && $arch['nodes'] > 1 ? ($arch['edges'] * 2) / ($arch['nodes'] * ($arch['nodes'] - 1)) : 0;
    $balanceRatio = $arch && $arch['arch_width'] > 0 ? $arch['arch_depth'] / $arch['arch_width'] : 0;
    
    // Architecture quality indicators
    $archQuality = $arch && $arch['impurity'] <= 0.1 ? 'Excellent' : ($arch && $arch['impurity'] <= 0.2 ? 'Good' : ($arch && $arch['impurity'] <= 0.3 ? 'Fair' : 'Poor'));
    $scalability = $arch && $arch['arch_depth'] <= 3 ? 'Highly Scalable' : ($arch && $arch['arch_depth'] <= 5 ? 'Moderately Scalable' : 'Limited Scalability');
    $designPattern = $balanceRatio <= 0.5 ? 'Balanced' : ($balanceRatio <= 1.0 ? 'Slightly Unbalanced' : 'Unbalanced');

    // ── Data Structure Complexity ─────────────────────────────────────────────
    $dscRows = $pdo->query("
        SELECT file_name, integer_vars, string_vars, array_vars,
               c1_integers, c2_strings, c3_arrays, total_complexity
        FROM   data_structure_complexity
        ORDER  BY total_complexity DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Enhanced data structure calculations
    $totalDSC = array_sum(array_column($dscRows, 'total_complexity'));
    $avgDSC = !empty($dscRows) ? round($totalDSC / count($dscRows), 2) : 0;
    $maxDSC = !empty($dscRows) ? max(array_column($dscRows, 'total_complexity')) : 0;
    $totalVars = !empty($dscRows) ? array_sum(array_column($dscRows, 'integer_vars')) + 
                                array_sum(array_column($dscRows, 'string_vars')) + 
                                array_sum(array_column($dscRows, 'array_vars')) : 0;
    $arrayComplexity = !empty($dscRows) ? array_sum(array_column($dscRows, 'c3_arrays')) : 0;
    
    // Data structure quality indicators
    $dscComplexity = $avgDSC <= 50 ? 'Low' : ($avgDSC <= 100 ? 'Medium' : ($avgDSC <= 200 ? 'High' : 'Very High'));
    $dataModelComplexity = $arrayComplexity > $totalVars * 0.3 ? 'Array-Heavy' : 'Balanced';
    $memoryEfficiency = $arrayComplexity > 0 ? 'Needs Review' : 'Good';

    echo json_encode([
        'status' => 'ok',
        'cyclomatic' => [
            'files'                => $ccRows,
            'avg_v'                => $avgV,
            'high_risk'            => $highRisk,
            'file_count'           => count($ccRows),
            'max_complexity'       => $maxComplexity,
            'min_complexity'       => $minComplexity,
            'distribution'         => $complexityDistribution,
            'complexity_score'     => $complexityScore,
            'maintainability_effort' => $maintainabilityEffort,
            'testing_complexity'   => $testingComplexity,
        ],
        'cohesion' => [
            'modules'           => $cohRows,
            'system_cohesion'   => $sysCohesion,
            'avg_internal'      => $avgInternalRelations,
            'avg_external'      => $avgExternalRelations,
            'cohesion_balance'  => $cohesionBalance,
            'cohesion_quality'  => $cohesionQuality,
            'modular_design'    => $modularDesign,
            'design_pattern'     => $designPattern,
        ],
        'coupling' => [
            'pairs'                => $cpRows,
            'global_coupling'       => $globalCoupling,
            'avg_coupling'         => $avgCoupling,
            'max_coupling'         => $maxCoupling,
            'avg_interconnections' => $avgInterconnections,
            'type_distribution'    => $couplingTypes,
            'coupling_quality'     => $couplingQuality,
            'module_independence'  => $moduleIndependence,
            'dominant_type'       => $dominantCouplingType,
        ],
        'information_flow' => [
            'modules'              => $flowRows,
            'avg_fan_in'          => $avgFanIn,
            'avg_fan_out'         => $avgFanOut,
            'max_ifc'             => $maxIFC,
            'flow_complexity'      => $flowComplexity,
            'flow_balance'         => $flowBalance,
            'flow_stability'       => $flowStability,
            'interface_complexity'  => $interfaceComplexity,
        ],
        'architecture' => $arch ? [
            'system_name'     => $arch['system_name'],
            'nodes'           => $arch['nodes'],
            'edges'           => $arch['edges'],
            'arch_depth'      => $arch['arch_depth'],
            'arch_width'      => $arch['arch_width'],
            'edge_node_ratio' => $arch['edge_node_ratio'],
            'impurity'        => $arch['impurity'],
            'complexity'      => $archComplexity,
            'density'         => $density,
            'balance_ratio'    => $balanceRatio,
            'arch_quality'    => $archQuality,
            'scalability'     => $scalability,
            'design_pattern'   => $designPattern,
        ] : (object)[],
        'data_structure' => [
            'files'               => $dscRows,
            'total_complexity'     => $totalDSC,
            'avg_complexity'       => $avgDSC,
            'max_complexity'       => $maxDSC,
            'total_variables'      => $totalVars,
            'array_complexity'     => $arrayComplexity,
            'dsc_complexity'      => $dscComplexity,
            'data_model_complexity' => $dataModelComplexity,
            'memory_efficiency'    => $memoryEfficiency,
        ],
        'insights' => [
            'overall_complexity' => [
                'score' => round((($avgV <= 15 ? 30 : ($avgV <= 25 ? 20 : 10)) + 
                                ($sysCohesion >= 60 ? 25 : ($sysCohesion >= 40 ? 15 : 5)) + 
                                ($globalCoupling <= 2.0 ? 25 : ($globalCoupling <= 3.0 ? 15 : 5)) + 
                                ($avgDSC <= 100 ? 20 : 10)), 1),
                'assessment' => ($avgV <= 15 && $sysCohesion >= 60 && $globalCoupling <= 2.0) ? 'Good' : 'Needs Improvement'
            ],
            'recommendations' => [
                $avgV > 20 ? 'Consider refactoring high complexity files to reduce cyclomatic complexity' : null,
                $sysCohesion < 50 ? 'Improve module cohesion by focusing on single responsibility principle' : null,
                $globalCoupling > 2.5 ? 'Reduce coupling between modules to improve maintainability' : null,
                $flowBalance > 40 ? 'Balance fan-in and fan-out for better module stability' : null,
                $arch && $arch['impurity'] > 0.3 ? 'Review architecture design to reduce impurity' : null,
                $avgDSC > 150 ? 'Simplify data structures to reduce complexity' : null
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
