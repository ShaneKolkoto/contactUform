<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../TestCase.php';
require_once __DIR__ . '/HttpTestCase.php';

use Tests\HttpTestCase;
use Tests\TestFailure;

$useColour = stream_isatty(STDOUT);
$green = $useColour ? "\033[32m" : '';
$red = $useColour ? "\033[31m" : '';
$dim = $useColour ? "\033[2m" : '';
$bold = $useColour ? "\033[1m" : '';
$reset = $useColour ? "\033[0m" : '';

$testFiles = glob(__DIR__ . '/*HttpTest.php') ?: [];
sort($testFiles);

if ($testFiles === []) {
    echo "No HTTP test files found in " . __DIR__ . "\n";
    exit(1);
}

echo "\n{$bold}HTTP Test Suite{$reset}\n";
echo str_repeat('=', 60) . "\n\n";

$totalTests = 0;
$totalAssertions = 0;
$totalPassed = 0;
$totalFailed = 0;
$totalErrored = 0;
$failures = [];
$started = microtime(true);

try {
    foreach ($testFiles as $file) {
        $className = 'Tests\\' . basename($file, '.php');
        require_once $file;

        if (!class_exists($className)) {
            echo "Skipping " . basename($file) . ": class {$className} not found\n";
            continue;
        }

        $reflection = new ReflectionClass($className);
        if (!$reflection->isSubclassOf(HttpTestCase::class) || $reflection->isAbstract()) {
            continue;
        }

        echo "{$bold}" . $reflection->getShortName() . "{$reset}\n";

        $methods = array_filter(
            $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
            fn(ReflectionMethod $m) => str_starts_with($m->getName(), 'test_')
        );
        $methodNames = array_map(fn($m) => $m->getName(), $methods);
        sort($methodNames);

        foreach ($methodNames as $methodName) {
            $totalTests++;
            $instance = $reflection->newInstance();
            $readable = str_replace('_', ' ', substr($methodName, 5));

            try {
                $instance->setUp();
            } catch (Throwable $e) {
                $totalErrored++;
                $failures[] = [
                    'test' => "{$reflection->getShortName()}::{$methodName}",
                    'message' => 'setUp failed — ' . $e->getMessage(),
                ];
                echo "  {$red}[ERR]{$reset}  {$readable}\n";
                echo "         setUp: " . $e->getMessage() . "\n";
                continue;
            }

            try {
                $instance->{$methodName}();
                $count = $instance->assertions();
                $totalAssertions += $count;
                $totalPassed++;
                $label = $count === 1 ? '1 assertion' : "{$count} assertions";
                echo "  {$green}[OK]{$reset}  {$readable} {$dim}({$label}){$reset}\n";
            } catch (TestFailure $e) {
                $totalAssertions += $instance->assertions();
                $totalFailed++;
                $failures[] = [
                    'test' => "{$reflection->getShortName()}::{$methodName}",
                    'message' => $e->getMessage(),
                ];
                echo "  {$red}[FAIL]{$reset} {$readable}\n";
                echo "         {$e->getMessage()}\n";
            } catch (Throwable $e) {
                $totalAssertions += $instance->assertions();
                $totalErrored++;
                $failures[] = [
                    'test' => "{$reflection->getShortName()}::{$methodName}",
                    'message' => get_class($e) . ' — ' . $e->getMessage(),
                ];
                echo "  {$red}[ERR]{$reset}  {$readable}\n";
                echo "         " . get_class($e) . ': ' . $e->getMessage() . "\n";
            }

            if (method_exists($instance, 'tearDown')) {
                try {
                    $instance->tearDown();
                } catch (Throwable $e) {
                    echo "         {$red}tearDown: " . $e->getMessage() . "{$reset}\n";
                }
            }
        }

        echo "\n";
    }
} finally {
    HttpTestCase::shutdownServer();
}

$duration = microtime(true) - $started;

echo str_repeat('─', 60) . "\n";
echo "{$totalTests} tests, {$totalAssertions} assertions in " . number_format($duration, 3) . "s\n";

if ($totalFailed === 0 && $totalErrored === 0) {
    echo "{$green}{$bold}All passed{$reset}\n";
} else {
    if ($totalPassed) echo "{$green}Passed:  {$totalPassed}{$reset}\n";
    if ($totalFailed) echo "{$red}Failed:  {$totalFailed}{$reset}\n";
    if ($totalErrored) echo "{$red}Errored: {$totalErrored}{$reset}\n";
}

if ($failures !== []) {
    echo "\n{$bold}Failures:{$reset}\n";
    foreach ($failures as $failure) {
        echo "  {$red}•{$reset} {$failure['test']}\n";
        echo "    {$failure['message']}\n";
    }
}

echo "\n";
exit($totalFailed === 0 && $totalErrored === 0 ? 0 : 1);