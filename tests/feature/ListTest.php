<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

class ListTest extends BaseTest
{
    public function testNoArgumentsShowsHelp()
    {
        $this->assertRegExp('/dsm.*metrics.*text.*uml.*/s', shell_exec(self::DEPHPEND.' list'));
    }
}
