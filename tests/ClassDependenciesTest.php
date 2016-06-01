<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\ClassDependencies
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 */
class ClassDependenciesTest extends \PHPUnit_Framework_TestCase
{
    public function testHasClass()
    {
        $dependencies = new ClassDependencies(new Clazz('SomeClass'));
        $this->assertEquals('SomeClass', $dependencies->clazz());
    }

    public function testHasDependencies()
    {
        $dependencies = new ClassDependencies(new Clazz('SomeClass'));
        $dependencies->addDependency(new Clazz('OtherClassA'));
        $dependencies->addDependency(new Clazz('OtherClassB'));
        $this->assertEquals('OtherClassA', $dependencies->dependencies()[0]);
        $this->assertEquals('OtherClassB', $dependencies->dependencies()[1]);
    }

    public function testCountable()
    {
        $dependencies = new ClassDependencies(new Clazz('SomeClass'));
        $dependencies->addDependency(new Clazz('OtherClassA'));
        $dependencies->addDependency(new Clazz('OtherClassB'));
        $this->assertCount(2, $dependencies);
    }
}
