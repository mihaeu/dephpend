<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

/**
 * @covers Mihaeu\PhpDependencies\Util\Functional
 */
class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    public function testCompose()
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

    public function testComposeWithoutArgumentsReturnsId()
    {
        $this->assertEquals(9, Functional::compose()(9));
    }
}
