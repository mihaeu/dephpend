<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\OS\ShellWrapper
 */
class ShellWrapperTest extends TestCase
{
    /**
     * Echo is installed on all Windows, Linux and Mac machines and should never fail.
     */
    public function testDetectsEcho(): void
    {
        assertEquals(0, (new ShellWrapper())->run('echo'));
    }

    public function testDetectsWhenApplicationNotInstalled(): void
    {
        assertNotEquals(0, (new ShellWrapper())->run('xjcsajhckjsdfhksdf'));
    }
}
