<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Util\Functional
 */
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
        assertEquals(8, Functional::compose(
            $incrementByOne,
            $multiplyByTwo,
            $multiplyByTwo
        )(1));
    }

    public function testComposeWithoutArgumentsReturnsId(): void
    {
        assertEquals(9, Functional::compose()(9));
    }
}
