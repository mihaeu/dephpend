<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Util\Functional::class)]
class FunctionalTest extends TestCase
{
    public function testCompose(): void
    {
        $incrementByOne = function ($x) {
            return $x + 1;
        };
        $multiplyByTwo = function ($x) {
            return $x * 2;
        };
        $this->assertEquals(8, Functional::compose(
            $incrementByOne,
            $multiplyByTwo,
            $multiplyByTwo
        )(1));
    }

    public function testComposeWithoutArgumentsReturnsId(): void
    {
        $this->assertEquals(9, Functional::compose()(9));
    }
}
