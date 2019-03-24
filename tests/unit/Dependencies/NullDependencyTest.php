<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\NullDependency
 */
class NullDependencyTest extends TestCase
{
    public function testReduceToDepth(): void
    {
        assertEquals(new NullDependency(), (new NullDependency())->reduceToDepth(99));
    }

    public function testReduceDepthFromLeftBy(): void
    {
        assertEquals(new NullDependency(), (new NullDependency())->reduceDepthFromLeftBy(99));
    }

    public function testEquals(): void
    {
        assertFalse((new NullDependency())->equals(new NullDependency()));
    }

    public function testToString(): void
    {
        assertEquals('', (new NullDependency())->__toString());
    }

    public function testNamespaze(): void
    {
        assertEquals(new Namespaze([]), (new NullDependency())->namespaze());
    }

    public function testInNamespazeIsFalseForEmptyNamespace(): void
    {
        assertFalse((new NullDependency())->inNamespaze(new Namespaze([])));
    }

    public function testInNamespazeIsFalseForEveryNamespace(): void
    {
        assertFalse((new NullDependency())->inNamespaze(new Namespaze(['A'])));
    }

    public function testCountIsAlwaysZero(): void
    {
        assertCount(0, new NullDependency());
    }

    public function testIsNotNamespaced(): void
    {
        assertFalse((new NullDependency())->isNamespaced());
    }
}
