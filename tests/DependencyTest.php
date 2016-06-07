<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\Dependency
 *
 * @covers Mihaeu\PhpDependencies\Clazz
 */
class DependencyTest extends \PHPUnit_Framework_TestCase
{
    public function testHasClazzFrom()
    {
        $this->assertEquals(new Clazz('From'), (new Dependency(new Clazz('From'), new Clazz('To')))->from());
    }

    public function testHasClazzTo()
    {
        $this->assertEquals(new Clazz('To'), (new Dependency(new Clazz('From'), new Clazz('To')))->to());
    }

    public function testToString()
    {
        $this->assertEquals('From --> To', new Dependency(new Clazz('From'), new Clazz('To')));
    }
}
