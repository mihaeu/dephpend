<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Util\Util
 */
class UtilTest extends TestCase
{
    public function testArrayMatchesAtLeastOnce(): void
    {
        $this->assertTrue(Util::array_once([1, 2, 3, 'tt'], function ($value, $index) {
            return $value === 'tt';
        }));
    }

    public function testArrayMatchesIndex(): void
    {
        $this->assertTrue(Util::array_once([1, 2, 3, 'tt'], function ($value, $index) {
            return $index === 3 && $value === 'tt';
        }));
    }

    public function testArrayMatchesNothing(): void
    {
        $this->assertFalse(Util::array_once([1, 2, 3, 'tt'], function ($value, $index) {
            return $value === 'xxx';
        }));
    }

    public function testReduceArrayWithKeys(): void
    {
        $this->assertEquals('0a1b2c3d', Util::reduce(['a', 'b', 'c', 'd'], function (string $carry, int $index, string $value) {
            return $carry.$index.$value;
        }, ''));
    }
}
