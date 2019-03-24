<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class DsmTest extends TestCase
{
    private const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';
    private const SRC = __DIR__.'/../../src';

    public function testCreatesSimpleDsmInHtml(): void
    {
        assertRegExp(
            '@\d: PhpParser</th><td>([1-9]\d*).+.+@s',
            shell_exec(self::DEPHPEND.' dsm '.self::SRC.' --no-classes -d 2 --format=html')
        );
    }
}
