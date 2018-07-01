<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\NullDependency
 */
class NullDependencyTest extends \PHPUnit\Framework\TestCase
{
    public function testReduceToDepth()
    {
        assertEquals(new NullDependency(), (new NullDependency())->reduceToDepth(99));
    }

    public function testReduceDepthFromLeftBy()
    {
        assertEquals(new NullDependency(), (new NullDependency())->reduceDepthFromLeftBy(99));
    }

    public function testEquals()
    {
        assertFalse((new NullDependency())->equals(new NullDependency()));
    }

    public function testToString()
    {
        assertEquals('', (new NullDependency())->__toString());
    }

    public function testNamespaze()
    {
        assertEquals(new Namespaze([]), (new NullDependency())->namespaze());
    }

    public function testInNamespazeIsFalseForEmptyNamespace()
    {
        assertFalse((new NullDependency())->inNamespaze(new Namespaze([])));
    }

    public function testInNamespazeIsFalseForEveryNamespace()
    {
        assertFalse((new NullDependency())->inNamespaze(new Namespaze(['A'])));
    }

    public function testCountIsAlwaysZero()
    {
        assertCount(0, new NullDependency());
    }

    public function testIsNotNamespaced()
    {
        assertFalse((new NullDependency())->isNamespaced());
    }
}
