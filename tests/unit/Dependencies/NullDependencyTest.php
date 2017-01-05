<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\NullDependency
 */
class NullDependencyTest extends \PHPUnit_Framework_TestCase
{
    public function testReduceToDepth()
    {
        $this->assertEquals(new NullDependency(), (new NullDependency())->reduceToDepth(99));
    }

    public function testReduceDepthFromLeftBy()
    {
        $this->assertEquals(new NullDependency(), (new NullDependency())->reduceDepthFromLeftBy(99));
    }

    public function testEquals()
    {
        $this->assertFalse((new NullDependency())->equals(new NullDependency()));
    }

    public function testToString()
    {
        $this->assertEquals('', (new NullDependency())->__toString());
    }

    public function testNamespaze()
    {
        $this->assertEquals(new Namespaze([]), (new NullDependency())->namespaze());
    }

    public function testInNamespazeIsFalseForEmptyNamespace()
    {
        $this->assertFalse((new NullDependency())->inNamespaze(new Namespaze([])));
    }

    public function testInNamespazeIsFalseForEveryNamespace()
    {
        $this->assertFalse((new NullDependency())->inNamespaze(new Namespaze(['A'])));
    }

    public function testCountIsAlwaysZero()
    {
        $this->assertCount(0, new NullDependency());
    }
    
    public function testIsNotNamespaced()
    {
        $this->assertFalse((new NullDependency())->isNamespaced());
    }
}
