<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\Namespaze
 */
class NamespazeTest extends TestCase
{
    public function testAcceptsEmptyNamespace(): void
    {
        assertEquals('', new Namespaze([]));
    }

    public function testAcceptsValidNamespaceParts(): void
    {
        assertEquals('a\b\c', new Namespaze(['a', 'b', 'c']));
    }

    public function testDetectsInvalidNamespaceParts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Namespaze([1]);
    }

    public function testDepthOfEmptyNamespaceIsZero(): void
    {
        assertCount(0, new Namespaze([]));
    }

    public function testDepthOfNamespace(): void
    {
        assertCount(2, new Namespaze(['A', 'B']));
    }

    public function testReducingDepthLowerThanPossibleProducesNullDependency(): void
    {
        assertInstanceOf(NullDependency::class, (new Namespaze(['Test']))->reduceToDepth(3));
    }

    public function testReduceToMaxDepth(): void
    {
        assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B', 'C', 'D']))->reduceToDepth(2));
    }

    public function testDoNotReduceForMaxDepthZero(): void
    {
        assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B']))->reduceToDepth(0));
    }

    public function testLeftReduceNamespace(): void
    {
        assertEquals(new Namespaze(['C']), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(2));
    }

    public function testReduceSameAsLengthProducesEmptyNamespace(): void
    {
        assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(3));
    }

    public function testReduceMoreThanLengthProducesEmptyNamespace(): void
    {
        assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(5));
    }

    public function testEquals(): void
    {
        assertTrue((new Namespaze(['A', 'B']))->equals(new Namespaze(['A', 'B'])));
        assertTrue((new Namespaze([]))->equals(new Namespaze([])));
        assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze(['A'])));
        assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze([])));
    }

    public function testPartsByIndex(): void
    {
        assertEquals(new Namespaze(['1']), (new Namespaze(['1', '2']))->parts()[0]);
        assertEquals(new Namespaze(['2']), (new Namespaze(['1', '2']))->parts()[1]);
    }

    public function testNamespazeReturnsItself(): void
    {
        assertEquals(new Namespaze(['1', '2']), (new Namespaze(['1', '2']))->namespaze());
    }

    public function testDetectsIfInOtherNamespace(): void
    {
        assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A'])));
    }

    public function testDetectsIfNotInOtherNamespace(): void
    {
        assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        assertFalse((new Namespaze([]))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze([])));
    }

    public function testEmptyNamespaceIsNotNamespaced(): void
    {
        assertFalse((new Namespaze([]))->isNamespaced());
    }
}
