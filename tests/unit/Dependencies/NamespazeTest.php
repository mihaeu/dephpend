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
        $this->assertEquals('', new Namespaze([]));
    }

    public function testAcceptsValidNamespaceParts(): void
    {
        $this->assertEquals('a\b\c', new Namespaze(['a', 'b', 'c']));
    }

    public function testDetectsInvalidNamespaceParts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        new Namespaze([1]);
    }

    public function testDepthOfEmptyNamespaceIsZero(): void
    {
        $this->assertCount(0, new Namespaze([]));
    }

    public function testDepthOfNamespace(): void
    {
        $this->assertCount(2, new Namespaze(['A', 'B']));
    }

    public function testReducingDepthLowerThanPossibleProducesNullDependency(): void
    {
        $this->assertInstanceOf(NullDependency::class, (new Namespaze(['Test']))->reduceToDepth(3));
    }

    public function testReduceToMaxDepth(): void
    {
        $this->assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B', 'C', 'D']))->reduceToDepth(2));
    }

    public function testDoNotReduceForMaxDepthZero(): void
    {
        $this->assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B']))->reduceToDepth(0));
    }

    public function testLeftReduceNamespace(): void
    {
        $this->assertEquals(new Namespaze(['C']), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(2));
    }

    public function testReduceSameAsLengthProducesEmptyNamespace(): void
    {
        $this->assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(3));
    }

    public function testReduceMoreThanLengthProducesEmptyNamespace(): void
    {
        $this->assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(5));
    }

    public function testEquals(): void
    {
        $this->assertTrue((new Namespaze(['A', 'B']))->equals(new Namespaze(['A', 'B'])));
        $this->assertTrue((new Namespaze([]))->equals(new Namespaze([])));
        $this->assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze(['A'])));
        $this->assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze([])));
    }

    public function testPartsByIndex(): void
    {
        $this->assertEquals(new Namespaze(['1']), (new Namespaze(['1', '2']))->parts()[0]);
        $this->assertEquals(new Namespaze(['2']), (new Namespaze(['1', '2']))->parts()[1]);
    }

    public function testNamespazeReturnsItself(): void
    {
        $this->assertEquals(new Namespaze(['1', '2']), (new Namespaze(['1', '2']))->namespaze());
    }

    public function testDetectsIfInOtherNamespace(): void
    {
        $this->assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        $this->assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A'])));
    }

    public function testDetectsIfNotInOtherNamespace(): void
    {
        $this->assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        $this->assertFalse((new Namespaze([]))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        $this->assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze([])));
    }

    public function testEmptyNamespaceIsNotNamespaced(): void
    {
        $this->assertFalse((new Namespaze([]))->isNamespaced());
    }
}
