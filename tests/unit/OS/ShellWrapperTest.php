<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

/**
 * @covers Mihaeu\PhpDependencies\OS\ShellWrapper
 */
class ShellWrapperTest extends \PHPUnit\Framework\TestCase
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
