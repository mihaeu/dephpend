<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class DsmTest extends TestCase
{
    public function testCreatesSimpleDsmInHtml(): void
    {
        $this->assertMatchesRegularExpression(
            '@\d: PhpParser</th><td>([1-9]\d*).+.+@s',
            shell_exec(DEPHPEND_BIN.' dsm '.SRC_PATH.' --no-classes --depth=2 --format=html')
        );
    }
}
