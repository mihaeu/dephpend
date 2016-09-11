<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencyPair
 *
 * @covers Mihaeu\PhpDependencies\Dependencies\Clazz
 */
class DependencyPairTest extends \PHPUnit_Framework_TestCase
{
    public function testHasClazzFrom()
    {
        $this->assertEquals(new Clazz('From'), (new DependencyPair(new Clazz('From'), (new DependencySet())->add(new Clazz('To'))))->from());
    }

    public function testHasClazzTo()
    {
        $this->assertEquals((new DependencySet())->add(new Clazz('To')), (new DependencyPair(new Clazz('From'), (new DependencySet())->add(new Clazz('To'))))->to());
    }

    public function testToString()
    {
        $this->assertEquals('From --> To', new DependencyPair(new Clazz('From'), (new DependencySet())->add(new Clazz('To'))));
    }

    public function testRefuseDependencyOnItself()
    {
        $this->assertEmpty((new DependencyPair(new Clazz('X')))
            ->addDependency(new Clazz('X'))
            ->to()
        );
    }

    public function testAddChildren()
    {
        $this->assertEquals(DependencyHelper::dependencyPair('A --> B'), (new DependencyPair(new Clazz('A')))->addDependency(new Clazz('B')));
    }
}
