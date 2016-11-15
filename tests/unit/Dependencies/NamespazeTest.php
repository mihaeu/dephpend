<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Exceptions\IndexOutOfBoundsException;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\Namespaze
 */
class NamespazeTest extends \PHPUnit_Framework_TestCase
{
    public function testAcceptsEmptyNamespace()
    {
        $this->assertEquals('', new Namespaze([]));
    }

    public function testAcceptsValidNamespaceParts()
    {
        $this->assertEquals('a\b\c', new Namespaze(['a', 'b', 'c']));
    }

    public function testDetectsInvalidNamespaceParts()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Namespaze([1]);
    }

    public function testDepthOfEmptyNamespaceIsZero()
    {
        $this->assertCount(0, new Namespaze([]));
    }

    public function testDepthOfNamespace()
    {
        $this->assertCount(2, new Namespaze(['A', 'B']));
    }

    public function testReducingDepthLowerThanPossibleProducesNullDependency()
    {
        $this->assertInstanceOf(NullDependency::class, (new Namespaze(['Test']))->reduceToDepth(3));
    }

    public function testReduceToMaxDepth()
    {
        $this->assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B', 'C', 'D']))->reduceToDepth(2));
    }

    public function testDoNotReduceForMaxDepthZero()
    {
        $this->assertEquals(new Namespaze(['A', 'B']), (new Namespaze(['A', 'B']))->reduceToDepth(0));
    }

    public function testLeftReduceNamespace()
    {
        $this->assertEquals(new Namespaze(['C']), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(2));
    }

    public function testReduceSameAsLengthProducesEmptyNamespace()
    {
        $this->assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(3));
    }

    public function testReduceMoreThanLengthProducesEmptyNamespace()
    {
        $this->assertEquals(new Namespaze([]), (new Namespaze(['A', 'B', 'C']))->reduceDepthFromLeftBy(5));
    }

    public function testEquals()
    {
        $this->assertTrue((new Namespaze(['A', 'B']))->equals(new Namespaze(['A', 'B'])));
        $this->assertTrue((new Namespaze([]))->equals(new Namespaze([])));
        $this->assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze(['A'])));
        $this->assertFalse((new Namespaze(['A', 'B']))->equals(new Namespaze([])));
    }

    public function testPartsByIndex()
    {
        $this->assertEquals(new Namespaze(['1']), (new Namespaze(['1', '2']))->parts()[0]);
        $this->assertEquals(new Namespaze(['2']), (new Namespaze(['1', '2']))->parts()[1]);
    }
    
    public function testNamespazeReturnsItself()
    {
        $this->assertEquals(new Namespaze(['1', '2']), (new Namespaze(['1', '2']))->namespaze());
    }
    
    public function testDetectsIfInOtherNamespace()
    {
        $this->assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        $this->assertTrue((new Namespaze(['A', 'b', 'T']))->inNamespaze(new Namespaze(['A'])));
    }

    public function testDetectsIfNotInOtherNamespace()
    {
        $this->assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        $this->assertFalse((new Namespaze([]))->inNamespaze(new Namespaze(['A', 'b', 'T'])));
        $this->assertFalse((new Namespaze(['XZY', 'b', 'T']))->inNamespaze(new Namespaze([])));
    }
}
