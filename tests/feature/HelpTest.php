<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\tests\feature;

class HelpTest extends \PHPUnit_Framework_TestCase
{
    const DEPHPEND = PHP_BINARY.' -n '.__DIR__.'/../../bin/dephpend';

    public function testNoArgumentsShowsHelp()
    {
        $this->assertRegExp('/version \d.*Usage:.*Options:.*commands:.*/s', shell_exec(self::DEPHPEND));
    }

    public function testHelpShowsHelp()
    {
        $this->assertRegExp('/Usage:.*Options:.*Help:.*/s', shell_exec(self::DEPHPEND.' help'));
    }

    public function testShowsHelpForCommand()
    {
        $this->assertRegExp('/Arguments:.*source.*Options:.*--internals.*/s', shell_exec(self::DEPHPEND.' help text'));
    }
}
