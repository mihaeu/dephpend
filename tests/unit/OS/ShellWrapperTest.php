<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\OS\ShellWrapper::class)]
class ShellWrapperTest extends TestCase
{
    /**
     * Echo is installed on all Windows, Linux and Mac machines and should never fail.
     */
    public function testDetectsEcho(): void
    {
        $this->assertEquals(0, (new ShellWrapper())->run('echo'));
    }

    public function testDetectsWhenApplicationNotInstalled(): void
    {
        $this->assertNotEquals(0, (new ShellWrapper())->run('xjcsajhckjsdfhksdf'));
    }
}
