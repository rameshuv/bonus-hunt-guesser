<?php

namespace PHPUnit\Framework;

class AssertionFailedError extends \Exception {}

abstract class TestCase {
    private int $assertions = 0;

    protected function setUp(): void {}
    protected function tearDown(): void {}

    /** @return int */
    public function getNumAssertions() {
        return $this->assertions;
    }

    protected function fail(string $message = ''): void {
        throw new AssertionFailedError($message !== '' ? $message : 'Failed asserting that condition is true.');
    }

    protected function increment(): void {
        $this->assertions++;
    }

    protected function assertTrue($condition, string $message = ''): void {
        $this->increment();
        if (true !== (bool) $condition) {
            $this->fail($message !== '' ? $message : 'Failed asserting that condition is true.');
        }
    }

    protected function assertFalse($condition, string $message = ''): void {
        $this->increment();
        if (false !== (bool) $condition) {
            $this->fail($message !== '' ? $message : 'Failed asserting that condition is false.');
        }
    }

    protected function assertSame($expected, $actual, string $message = ''): void {
        $this->increment();
        if ($expected !== $actual) {
            $this->fail($message !== '' ? $message : sprintf('Failed asserting that %s is identical to %s.', var_export($actual, true), var_export($expected, true)));
        }
    }

    protected function assertEqualsWithDelta($expected, $actual, $delta, string $message = ''): void {
        $this->increment();
        if (abs((float) $expected - (float) $actual) > (float) $delta) {
            $this->fail($message !== '' ? $message : sprintf('Failed asserting that %.4f matches %.4f with delta %.4f.', $actual, $expected, $delta));
        }
    }

    protected function assertArrayHasKey($key, $array, string $message = ''): void {
        $this->increment();
        if (!is_array($array) || !array_key_exists($key, $array)) {
            $this->fail($message !== '' ? $message : sprintf('Failed asserting that an array has the key %s.', var_export($key, true)));
        }
    }

    protected function assertCount(int $expected, $array, string $message = ''): void {
        $this->increment();
        if (count((array) $array) !== $expected) {
            $this->fail($message !== '' ? $message : sprintf('Failed asserting that array has %d elements.', $expected));
        }
    }

    protected function assertNull($actual, string $message = ''): void {
        $this->increment();
        if (!is_null($actual)) {
            $this->fail($message !== '' ? $message : 'Failed asserting that value is null.');
        }
    }

    protected function assertNotNull($actual, string $message = ''): void {
        $this->increment();
        if (is_null($actual)) {
            $this->fail($message !== '' ? $message : 'Failed asserting that value is not null.');
        }
    }

    protected function assertContains($needle, $haystack, string $message = ''): void {
        $this->increment();
        $found = false;
        if (is_array($haystack)) {
            $found = in_array($needle, $haystack, true);
        } elseif (is_string($haystack)) {
            $found = false !== strpos($haystack, (string) $needle);
        }

        if (!$found) {
            $this->fail($message !== '' ? $message : 'Failed asserting that the haystack contains the needle.');
        }
    }

    protected function assertEmpty($actual, string $message = ''): void {
        $this->increment();
        if (!empty($actual)) {
            $this->fail($message !== '' ? $message : 'Failed asserting that value is empty.');
        }
    }

    protected function assertNotEmpty($actual, string $message = ''): void {
        $this->increment();
        if (empty($actual)) {
            $this->fail($message !== '' ? $message : 'Failed asserting that value is not empty.');
        }
    }

    protected function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void {
        $this->increment();
        if ($actual < $expected) {
            $this->fail($message !== '' ? $message : sprintf('Failed asserting that %s is greater than or equal to %s.', var_export($actual, true), var_export($expected, true)));
        }
    }

    protected function assertStringContainsString(string $needle, string $haystack, string $message = ''): void {
        $this->increment();
        if (strpos($haystack, $needle) === false) {
            $this->fail($message !== '' ? $message : sprintf('Failed asserting that "%s" contains "%s".', $haystack, $needle));
        }
    }
}
