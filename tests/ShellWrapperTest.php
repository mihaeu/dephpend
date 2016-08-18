<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\ShellWrapper
 */
class ShellWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Echo is installed on all Windows, Linux and Mac machines and should never fail.
     */
    public function testDetectsEcho()
    {
        $this->assertEquals(0, (new ShellWrapper())->run('echo'));
    }

    public function testDetectsWhenApplicationNotInstalled()
    {
        $this->assertNotEquals(0, (new ShellWrapper())->run('xjcsajhckjsdfhksdf'));
    }
}
