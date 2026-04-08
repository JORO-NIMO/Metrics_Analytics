<?php
// getsizemetrics.php
// Lecture 05: Software Size Metrics
// Returns LOC, Halstead, Function Points, and Reuse data for the admin dashboard

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'config.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();

    // ── LOC ───────────────────────────────────────────────────────────────────
    $locRows = $pdo->query("
        SELECT file_name, file_path, total_loc, ncloc, cloc, blank_lines, comment_density
        FROM   loc_measurements
        ORDER  BY total_loc DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $totalLOC   = array_sum(array_column($locRows, 'total_loc'));
    $totalNCLOC = array_sum(array_column($locRows, 'ncloc'));
    $totalCLOC  = array_sum(array_column($locRows, 'cloc'));
    $avgDensity = $totalLOC > 0 ? round(($totalCLOC / $totalLOC) * 100, 2) : 0;
    
    // Enhanced LOC calculations
    $avgLOCPerFile = count($locRows) > 0 ? round($totalLOC / count($locRows), 0) : 0;
    $codeQuality = $totalLOC > 0 ? round(($totalNCLOC / $totalLOC) * 100, 2) : 0;
    $largestFile = !empty($locRows) ? $locRows[0] : null;
    $smallestFile = !empty($locRows) ? end($locRows) : null;
    
    // Size classification
    $sizeCategory = $totalLOC < 1000 ? 'Small' : ($totalLOC < 10000 ? 'Medium' : ($totalLOC < 50000 ? 'Large' : 'Very Large'));
    $maintainabilityIndex = $avgDensity >= 15 ? 'Good' : ($avgDensity >= 10 ? 'Fair' : 'Poor');

    // ── Halstead ──────────────────────────────────────────────────────────────
    $halRows = $pdo->query("
        SELECT file_name, mu1, mu2, vocabulary, length_n,
               estimated_length, volume, difficulty, effort
        FROM   halstead_metrics
        ORDER  BY effort DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Enhanced Halstead calculations
    $totalVolume = array_sum(array_column($halRows, 'volume'));
    $totalEffort = array_sum(array_column($halRows, 'effort'));
    $avgDifficulty = !empty($halRows) ? round(array_sum(array_column($halRows, 'difficulty')) / count($halRows), 2) : 0;
    $avgVocabulary = !empty($halRows) ? round(array_sum(array_column($halRows, 'vocabulary')) / count($halRows), 0) : 0;
    $mostComplexFile = !empty($halRows) ? $halRows[0] : null;
    
    // Halstead quality indicators
    $complexityLevel = $totalEffort < 50000 ? 'Low' : ($totalEffort < 150000 ? 'Medium' : ($totalEffort < 300000 ? 'High' : 'Very High'));
    $codeReadability = $avgDifficulty < 15 ? 'Excellent' : ($avgDifficulty < 25 ? 'Good' : ($avgDifficulty < 35 ? 'Fair' : 'Poor'));

    // ── Function Points ───────────────────────────────────────────────────────
    $fpRows = $pdo->query("
        SELECT component_type, component_name, description,
               count_value, weight, weighted_value
        FROM   function_points
        ORDER  BY FIELD(component_type,'EI','EO','EQ','ILF','EIF')
    ")->fetchAll(PDO::FETCH_ASSOC);

    $ufc = array_sum(array_column($fpRows, 'weighted_value'));

    // Group by type for summary cards
    $fpSummary = [];
    foreach ($fpRows as $r) {
        $t = $r['component_type'];
        if (!isset($fpSummary[$t])) {
            $fpSummary[$t] = ['type' => $t, 'count' => 0, 'weighted_total' => 0];
        }
        $fpSummary[$t]['count']          += (int) $r['count_value'];
        $fpSummary[$t]['weighted_total'] += (int) $r['weighted_value'];
    }

    // ── Reuse ─────────────────────────────────────────────────────────────────
    $reuseRows = $pdo->query("
        SELECT file_name, total_loc, verbatim_loc, slightly_modified_loc,
               extensively_modified_loc, new_loc, reuse_level, reuse_density
        FROM   reuse_metrics
        ORDER  BY reuse_level DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Enhanced reuse calculations
    $totalReuseLOC = array_sum(array_column($reuseRows, 'verbatim_loc')) + 
                    array_sum(array_column($reuseRows, 'slightly_modified_loc')) + 
                    array_sum(array_column($reuseRows, 'extensively_modified_loc'));
    $avgReuseLevel = !empty($reuseRows) ? round(array_sum(array_column($reuseRows, 'reuse_level')) / count($reuseRows), 1) : 0;
    $newCodePercentage = $totalLOC > 0 ? round((array_sum(array_column($reuseRows, 'new_loc')) / $totalLOC) * 100, 1) : 0;
    
    // Reuse quality indicators
    $reuseEfficiency = $avgReuseLevel >= 50 ? 'Excellent' : ($avgReuseLevel >= 30 ? 'Good' : ($avgReuseLevel >= 15 ? 'Fair' : 'Poor'));
    $developmentStrategy = $newCodePercentage > 70 ? 'Mostly New Development' : ($newCodePercentage > 40 ? 'Balanced' : 'Reuse-Oriented');

    echo json_encode([
        'status' => 'ok',
        'loc' => [
            'files'               => $locRows,
            'total_loc'           => $totalLOC,
            'total_ncloc'         => $totalNCLOC,
            'total_cloc'          => $totalCLOC,
            'avg_comment_density' => $avgDensity,
            'file_count'          => count($locRows),
            'avg_loc_per_file'    => $avgLOCPerFile,
            'code_quality'        => $codeQuality,
            'size_category'       => $sizeCategory,
            'maintainability'     => $maintainabilityIndex,
            'largest_file'        => $largestFile,
            'smallest_file'       => $smallestFile,
        ],
        'halstead' => [
            'files'               => $halRows,
            'total_volume'        => $totalVolume,
            'total_effort'        => $totalEffort,
            'avg_difficulty'      => $avgDifficulty,
            'avg_vocabulary'      => $avgVocabulary,
            'most_complex_file'   => $mostComplexFile,
            'complexity_level'    => $complexityLevel,
            'code_readability'    => $codeReadability,
        ],
        'function_points' => [
            'detail'  => $fpRows,
            'summary' => array_values($fpSummary),
            'ufc'     => $ufc,
        ],
        'reuse' => [
            'files'                => $reuseRows,
            'total_reuse_loc'      => $totalReuseLOC,
            'avg_reuse_level'      => $avgReuseLevel,
            'new_code_percentage'  => $newCodePercentage,
            'reuse_efficiency'     => $reuseEfficiency,
            'development_strategy' => $developmentStrategy,
        ],
        'insights' => [
            'overall_quality' => [
                'score' => round(($codeQuality + $avgDensity + ($avgReuseLevel/5)) / 3, 1),
                'assessment' => $codeQuality >= 70 && $avgDensity >= 10 ? 'Good' : 'Needs Improvement'
            ],
            'recommendations' => [
                $avgDensity < 10 ? 'Increase code documentation for better maintainability' : null,
                $codeQuality < 60 ? 'Focus on reducing non-code lines (comments, blanks)' : null,
                $avgReuseLevel < 20 ? 'Consider more code reuse to improve development efficiency' : null,
                $complexityLevel === 'Very High' ? 'Review most complex files for potential refactoring' : null
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
