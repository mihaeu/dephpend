<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Exceptions\IndexOutOfBoundsException;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\Namespaze
 */
class NamespazeTest extends \PHPUnit\Framework\TestCase
{
    public function testAcceptsEmptyNamespace()
    {
        assertEquals('', new Namespaze([]));
    }

    public function testAcceptsValidNamespaceParts()
    {
        assertEquals('a\b\c', new Namespaze(['a', 'b', 'c']));
    }

    public function testDetectsInvalidNamespaceParts()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Namespaze([1]);
    }

    public function testDepthOfEmptyNamespaceIsZero()
    {
        assertCount(0, new Namespaze([]));
    }

    public function testDepthOfNamespace()
    {
        assertCount(2, new Namespaze(['A', 'B']));
    }

    public function testReducingDepthLowerThanPossibleProducesNullDependency()
    {
        assertInstanceOf(NullDependency::class, (new Namespaze(['Test']))->reduceToDepth(3));
    }

    public function testReduceToMaxDepth()
    {
        assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B', 'C', 'D']))->reduceToDepth(2));
    }

    public function testDoNotReduceForMaxDepthZero()
    {
        assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B']))->reduceToDepth(0));
    }

    public function testLeftReduceNamespace()
    {
        assertEquals(new Namespaze(['C']), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(2));
    }

    public function testReduceSameAsLengthProducesEmptyNamespace()
    {
        assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(3));
    }

    public function testReduceMoreThanLengthProducesEmptyNamespace()
    {
        assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(5));
    }

    public function testEquals()
    {
        assertTrue((new Namespaze(['A', 'B']))->equals(new Namespaze(['A', 'B'])));
        assertTrue((new Namespaze([]))->equals(new Namespaze([])));
        assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze(['A'])));
        assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze([])));
    }

    public function testPartsByIndex()
    {
        assertEquals(new Namespaze(['1']), (new Namespaze(['1', '2']))->parts()[0]);
        assertEquals(new Namespaze(['2']), (new Namespaze(['1', '2']))->parts()[1]);
    }

    public function testNamespazeReturnsItself()
    {
        assertEquals(new Namespaze(['1', '2']), (new Namespaze(['1', '2']))->namespaze());
    }

    public function testDetectsIfInOtherNamespace()
    {
        assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A'])));
    }

    public function testDetectsIfNotInOtherNamespace()
    {
        assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        assertFalse((new Namespaze([]))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze([])));
    }

    public function testEmptyNamespaceIsNotNamespaced()
    {
        assertFalse((new Namespaze([]))->isNamespaced());
    }
}
