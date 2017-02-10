<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class ListTest extends TestCase
{
    const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';

    public function testNoArgumentsShowsHelp()
    {
        $this->assertRegExp('/dsm.*metrics.*text.*uml.*/s', shell_exec(self::DEPHPEND.' list'));
    }
}
