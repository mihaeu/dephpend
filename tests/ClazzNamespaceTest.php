<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\ClazzNamespace
 */
class ClazzNamespaceTest extends \PHPUnit_Framework_TestCase
{
    public function testAcceptsEmptyNamespace()
    {
        $this->assertEquals('', new ClazzNamespace([]));
    }

    public function testAcceptsValidNamespaceParts()
    {
        $this->assertEquals('a\b\c', new ClazzNamespace(['a', 'b', 'c']));
    }

    public function testDetectsInvalidNamespaceParts()
    {
        $this->expectException(\InvalidArgumentException::class);
        new ClazzNamespace([1]);
    }

    public function testDepthOfEmptyNamespaceIsZero()
    {
        $this->assertEquals(0, (new ClazzNamespace([]))->depth());
    }

    public function testDepthOfNamespace()
    {
        $this->assertEquals(2, (new ClazzNamespace(['A', 'B']))->depth());
    }

    public function testReduceToMaxDepth()
    {
        $this->assertEquals(new ClazzNamespace(['A', 'B']), (new ClazzNamespace(['A', 'B', 'C', 'D']))->reduceToDepth(2));
    }

    public function testDoNotReduceForMaxDepthZero()
    {
        $this->assertEquals(new ClazzNamespace(['A', 'B']), (new ClazzNamespace(['A', 'B']))->reduceToDepth(0));
    }
}
