<?php
/**
 * Test runner.
 *
 * Discovers every *Test.php file under tests/, instantiates the class,
 * runs each public test* method (with setUp/tearDown around it), and
 * prints a summary. Exits non-zero if any test failed.
 *
 * Usage: php tests/run.php [path/to/filter]
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$filter = $argv[1] ?? null;

$start = microtime(true);
$totalPassed = 0;
$totalFailed = 0;
$totalSkipped = 0;
$totalAssertions = 0;
$failures = [];

$testFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($iterator as $file) {
    if ($file->isFile() && preg_match('/Test\.php$/', $file->getFilename())) {
        $testFiles[] = $file->getPathname();
    }
}
sort($testFiles);

foreach ($testFiles as $testFile) {
    if ($filter && !str_contains($testFile, $filter)) {
        continue;
    }

    $classesBefore = get_declared_classes();
    require_once $testFile;
    $newClasses = array_diff(get_declared_classes(), $classesBefore);

    foreach ($newClasses as $class) {
        $reflection = new ReflectionClass($class);
        if (!$reflection->isSubclassOf('TestCase') || $reflection->isAbstract()) {
            continue;
        }

        $methods = array_filter(
            $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
            fn($m) => str_starts_with($m->getName(), 'test')
        );

        if (empty($methods)) {
            continue;
        }

        echo "\n\033[1m" . $class . "\033[0m (" . basename($testFile) . ")\n";

        foreach ($methods as $method) {
            $instance = $reflection->newInstance();
            $methodName = $method->getName();

            try {
                $instance->setUp();
                $instance->$methodName();
                $instance->tearDown();
                $totalAssertions += $instance->assertions;
                $totalPassed++;
                echo "  \033[32m✓\033[0m {$methodName}\n";
            } catch (SkipTestException $e) {
                $instance->tearDown();
                $totalSkipped++;
                echo "  \033[33m-\033[0m {$methodName} (skipped: {$e->getMessage()})\n";
            } catch (Throwable $e) {
                try { $instance->tearDown(); } catch (Throwable $ignore) {}
                $totalFailed++;
                $failures[] = "{$class}::{$methodName}\n    " . $e->getMessage()
                    . "\n    at " . $e->getFile() . ':' . $e->getLine();
                echo "  \033[31m✗\033[0m {$methodName}\n";
                echo "    \033[31m" . $e->getMessage() . "\033[0m\n";
            }
        }
    }
}

$elapsed = round(microtime(true) - $start, 3);

echo "\n";
echo str_repeat('=', 60) . "\n";

if ($totalFailed > 0) {
    echo "\n\033[1;31mFailures:\033[0m\n";
    foreach ($failures as $f) {
        echo "  " . str_replace("\n", "\n  ", $f) . "\n\n";
    }
}

$summary = sprintf(
    "Tests: %d passed, %d failed, %d skipped | %d assertions | %.3fs",
    $totalPassed,
    $totalFailed,
    $totalSkipped,
    $totalAssertions,
    $elapsed
);

if ($totalFailed > 0) {
    echo "\n\033[1;31mFAIL\033[0m: $summary\n";
    exit(1);
}

echo "\n\033[1;32mOK\033[0m: $summary\n";
exit(0);
