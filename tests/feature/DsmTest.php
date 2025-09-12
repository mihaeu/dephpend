<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class DsmTest extends TestCase
{
    public function testCreatesSimpleDsmInHtml(): void
    {
        $this->assertMatchesRegularExpression(
            '@\d: PhpParser</th><td>([1-9]\d*).+.+@s',
            shell_exec(sprintf('"%s" -n "%s" dsm "%s" --no-classes --depth=2 --format=html', PHP_BINARY, DEPHPEND_BIN, SRC_PATH))
        );
    }
}
