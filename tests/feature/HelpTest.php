<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

use PHPUnit\Framework\TestCase;

class HelpTest extends TestCase
{
    private const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';

    public function testNoArgumentsShowsHelp(): void
    {
        self::assertRegExp('/command \[options\] \[arguments\].*/s', shell_exec(self::DEPHPEND));
    }

    public function testHelpShowsHelp(): void
    {
        self::assertRegExp('/Usage:.*Options:.*Help:.*/s', shell_exec(self::DEPHPEND.' help'));
    }

    public function testShowsHelpForCommand(): void
    {
        self::assertRegExp('/Arguments:.*source.*Options:.*--internals.*/s', shell_exec(self::DEPHPEND.' help text'));
    }
}
