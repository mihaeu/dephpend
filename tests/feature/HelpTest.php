<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    public function testNoArgumentsShowsHelp(): void
    {
        self::assertRegExp('/command \[options\] \[arguments\].*/s', shell_exec(DEPHPEND_BIN));
    }

    public function testHelpShowsHelp(): void
    {
        self::assertRegExp('/Usage:.*Options:.*Help:.*/s', shell_exec(DEPHPEND_BIN.' help'));
    }

    public function testShowsHelpForCommand(): void
    {
        self::assertRegExp('/Arguments:.*source.*Options:.*--internals.*/s', shell_exec(DEPHPEND_BIN.' help text'));
    }
}
