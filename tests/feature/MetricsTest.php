<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class MetricsTest extends TestCase
{
    public function testComputeMetricsForDephpend(): void
    {
        $this->assertRegExp(
            '/Classes:.*\d\d.*Abstract classes:.*\d+.*Abstractness:.*\d\.\d+/s',
            shell_exec(DEPHPEND_BIN.' metrics '.SRC_PATH)
        );
    }
}
