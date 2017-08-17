<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

class MetricsTest extends BaseTest
{
    public function testComputeMetricsForDephpend()
    {
        $this->assertRegExp(
            '/Classes:.*\d\d.*Abstract classes:.*\d+.*Abstractness:.*\d\.\d+/s',
            shell_exec(self::DEPHPEND.' metrics '.self::SRC)
        );
    }
}
