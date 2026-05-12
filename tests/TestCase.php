<?php
/**
 * Tiny zero-dependency TestCase.
 *
 * Each public test* method runs inside a DB transaction (when the database
 * is available) and is rolled back at the end so fixtures don't leak
 * between tests. Tests that need the DB call $this->requireDb() in their
 * first line; if the connection is unavailable they're skipped, not
 * counted as failures.
 */

declare(strict_types=1);

abstract class TestCase
{
    /** @var array<int, string> */
    public array $failures = [];
    /** @var array<int, string> */
    public array $skipped = [];
    public int $assertions = 0;

    protected ?\PDO $db = null;
    protected bool $dbAvailable = false;

    public function setUp(): void
    {
        $this->tryConnect();
        if ($this->dbAvailable) {
            $this->db->beginTransaction();
        }
    }

    public function tearDown(): void
    {
        if ($this->dbAvailable && $this->db && $this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    private function tryConnect(): void
    {
        try {
            $this->db = \App\Core\Database::getInstance();
            $this->dbAvailable = true;
        } catch (\Throwable $e) {
            $this->dbAvailable = false;
            $this->db = null;
        }
    }

    protected function requireDb(): void
    {
        if (!$this->dbAvailable) {
            throw new SkipTestException('Database not available');
        }
    }

    public function assertTrue($value, string $message = ''): void
    {
        $this->assertions++;
        if ($value !== true) {
            throw new AssertionFailedException($message ?: 'Expected true, got ' . var_export($value, true));
        }
    }

    public function assertFalse($value, string $message = ''): void
    {
        $this->assertions++;
        if ($value !== false) {
            throw new AssertionFailedException($message ?: 'Expected false, got ' . var_export($value, true));
        }
    }

    public function assertEquals($expected, $actual, string $message = ''): void
    {
        $this->assertions++;
        if ($expected != $actual) {
            throw new AssertionFailedException(
                $message ?: 'Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true)
            );
        }
    }

    public function assertSame($expected, $actual, string $message = ''): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new AssertionFailedException(
                $message ?: 'Expected ' . var_export($expected, true) . ' (strict), got ' . var_export($actual, true)
            );
        }
    }

    public function assertNull($actual, string $message = ''): void
    {
        $this->assertions++;
        if ($actual !== null) {
            throw new AssertionFailedException($message ?: 'Expected null, got ' . var_export($actual, true));
        }
    }

    public function assertNotNull($actual, string $message = ''): void
    {
        $this->assertions++;
        if ($actual === null) {
            throw new AssertionFailedException($message ?: 'Expected non-null value');
        }
    }

    public function assertContains($needle, array $haystack, string $message = ''): void
    {
        $this->assertions++;
        if (!in_array($needle, $haystack, true)) {
            throw new AssertionFailedException(
                $message ?: var_export($needle, true) . ' not found in ' . var_export($haystack, true)
            );
        }
    }

    public function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertions++;
        if (!str_contains($haystack, $needle)) {
            throw new AssertionFailedException(
                $message ?: "Expected to find '$needle' in '$haystack'"
            );
        }
    }

    /**
     * Run a closure and assert it throws an exception of the given class.
     * Returns the caught exception for further assertions.
     */
    public function expectException(string $exceptionClass, callable $fn): \Throwable
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            if (!($e instanceof $exceptionClass)) {
                throw new AssertionFailedException(
                    "Expected $exceptionClass, got " . get_class($e) . ': ' . $e->getMessage()
                );
            }
            $this->assertions++;
            return $e;
        }

        throw new AssertionFailedException("Expected $exceptionClass to be thrown, but no exception was raised");
    }
}

class AssertionFailedException extends \RuntimeException {}
class SkipTestException extends \RuntimeException {}
