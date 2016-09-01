<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

/**
 * @covers Mihaeu\PhpDependencies\Util\Util
 */
class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayMatchesAtLeastOnce()
    {
        $this->assertTrue(Util::array_once([1, 2, 3, 'tt'], function ($value, $index) {
            return $value === 'tt';
        }));
    }

    public function testArrayMatchesIndex()
    {
        $this->assertTrue(Util::array_once([1, 2, 3, 'tt'], function ($value, $index) {
            return $index === 3 && $value === 'tt';
        }));
    }

    public function testArrayMatchesNothing()
    {
        $this->assertFalse(Util::array_once([1, 2, 3, 'tt'], function ($value, $index) {
            return $value === 'xxx';
        }));
    }
}
