<?php
// getsizemetrics.php
// Lecture 05: Software Size Metrics
// Returns LOC, Halstead, Function Points, and Reuse data for the admin dashboard

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'config.php';

try {
    $pdo = getDBConnection();

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

    // ── Halstead ──────────────────────────────────────────────────────────────
    $halRows = $pdo->query("
        SELECT file_name, mu1, mu2, vocabulary, length_n,
               estimated_length, volume, difficulty, effort
        FROM   halstead_metrics
        ORDER  BY effort DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

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

    echo json_encode([
        'status' => 'ok',
        'loc' => [
            'files'               => $locRows,
            'total_loc'           => $totalLOC,
            'total_ncloc'         => $totalNCLOC,
            'total_cloc'          => $totalCLOC,
            'avg_comment_density' => $avgDensity,
            'file_count'          => count($locRows),
        ],
        'halstead' => [
            'files' => $halRows,
        ],
        'function_points' => [
            'detail'  => $fpRows,
            'summary' => array_values($fpSummary),
            'ufc'     => $ufc,
        ],
        'reuse' => [
            'files' => $reuseRows,
        ],
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
