<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\Namespaze
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
}
