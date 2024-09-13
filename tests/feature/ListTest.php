<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class ListTest extends TestCase
{
    public function testNoArgumentsShowsHelp(): void
    {
        $this->assertRegExp('/dsm.*metrics.*text.*uml.*/s', shell_exec(DEPHPEND_BIN.' list'));
    }
}
