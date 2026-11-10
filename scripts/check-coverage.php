<?php

/**
 * Fails (exit 1) when the line coverage in a Clover report is below a minimum.
 *
 * PHPUnit can write coverage reports but cannot enforce a threshold, so CI
 * runs this right after the test suite:
 *
 *     php scripts/check-coverage.php coverage.xml 100
 */
if ($argc !== 3 || ! is_numeric($argv[2])) {
    fwrite(STDERR, "Usage: php scripts/check-coverage.php <clover.xml> <minimum-percent>\n");
    exit(2);
}

[, $report, $minimum] = $argv;

$xml = is_file($report) ? simplexml_load_file($report) : false;
$metrics = $xml === false ? null : $xml->project->metrics;

if ($metrics === null || ! isset($metrics['statements'], $metrics['coveredstatements'])) {
    fwrite(STDERR, "Could not read the coverage metrics from \"{$report}\".\n");
    exit(2);
}

$statements = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];
$percent = $statements === 0 ? 100.0 : $covered / $statements * 100;

printf("Line coverage: %.2f%% (%d/%d lines), minimum: %s%%\n", $percent, $covered, $statements, $minimum);

if ($percent < (float) $minimum) {
    fwrite(STDERR, "Coverage is below the minimum.\n");
    exit(1);
}
