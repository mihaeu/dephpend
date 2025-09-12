<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class HelpTest extends TestCase
{
    public function testNoArgumentsShowsHelp(): void
    {
        $this->assertMatchesRegularExpression('/command \[options\] \[arguments\].*/s', shell_exec(sprintf('"%s" -n "%s"', PHP_BINARY, DEPHPEND_BIN)));
    }

    public function testHelpShowsHelp(): void
    {
        $this->assertMatchesRegularExpression('/Usage:.*Options:.*Help:.*/s', shell_exec(sprintf('"%s" -n "%s" help', PHP_BINARY, DEPHPEND_BIN)));
    }

    public function testShowsHelpForCommand(): void
    {
        $this->assertMatchesRegularExpression('/Arguments:.*source.*Options:.*--internals.*/s', shell_exec(sprintf('"%s" help text', DEPHPEND_BIN)));
    }
}
