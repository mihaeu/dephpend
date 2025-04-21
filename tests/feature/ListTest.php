<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class ListTest extends TestCase
{
    public function testNoArgumentsShowsHelp(): void
    {
        $this->assertMatchesRegularExpression('/dsm.*metrics.*text.*uml.*/s', shell_exec(DEPHPEND_BIN.' list'));
    }
}
