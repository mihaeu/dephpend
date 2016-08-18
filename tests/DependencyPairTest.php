<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyPair
 *
 * @covers Mihaeu\PhpDependencies\Clazz
 */
class DependencyPairTest extends \PHPUnit_Framework_TestCase
{
    public function testHasClazzFrom()
    {
        $this->assertEquals(new Clazz('From'), (new DependencyPair(new Clazz('From'), new Clazz('To')))->from());
    }

    public function testHasClazzTo()
    {
        $this->assertEquals(new Clazz('To'), (new DependencyPair(new Clazz('From'), new Clazz('To')))->to());
    }

    public function testToString()
    {
        $this->assertEquals('From --> To', new DependencyPair(new Clazz('From'), new Clazz('To')));
    }
}
