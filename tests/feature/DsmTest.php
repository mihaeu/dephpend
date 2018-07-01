<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class DsmTest extends TestCase
{
    const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    const SRC = __DIR__.'/../../src';

    public function testCreatesSimpleDsmInHtml()
    {
        assertRegExp(
            '@\d: PhpParser</th><td>([1-9]\d*).+.+@s',
            shell_exec(self::DEPHPEND.' dsm '.self::SRC.' --no-classes -d 2 --format=html')
        );
    }
}
