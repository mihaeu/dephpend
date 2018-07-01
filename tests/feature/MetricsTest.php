<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class MetricsTest extends TestCase
{
    const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    const SRC = __DIR__.'/../../src';

    public function testComputeMetricsForDephpend()
    {
        assertRegExp(
            '/Classes:.*\d\d.*Abstract classes:.*\d+.*Abstractness:.*\d\.\d+/s',
            shell_exec(self::DEPHPEND.' metrics '.self::SRC)
        );
    }
}
