<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper as H;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\Clazz
 * @covers Mihaeu\PhpDependencies\Dependencies\ClazzLike
 */
class ClazzTest extends \PHPUnit_Framework_TestCase
{
    public function testHasValue()
    {
        $this->assertEquals('Name', new Clazz('Name'));
        $this->assertEquals('Name', (new Clazz('Name'))->toString());
    }

    public function testNamespace()
    {
        $this->assertEquals(new Namespaze(['A', 'a']), (new Clazz('Name', new Namespaze(['A', 'a'])))->namespaze());
    }

    public function testToStringWithNamespace()
    {
        $this->assertEquals('A\\a\\ClassA', (new Clazz('ClassA', new Namespaze(['A', 'a']))));
    }

    public function testEquals()
    {
        $this->assertTrue((new Clazz('A'))->equals(new Clazz('A')));
    }

    public function testDetectsIfClassHasNamespace()
    {
        $this->assertTrue((new Clazz('Class', new Namespaze(['A'])))->hasNamespace());
    }

    public function testDetectsIfClassHasNoNamespace()
    {
        $this->assertFalse((new Clazz('Class'))->hasNamespace());
    }

    public function testDepthWithoutNamespaceIsOne()
    {
        $this->assertCount(1, new Clazz('A'));
    }

    public function testDepthWithNamespace()
    {
        $this->assertCount(3, new Clazz('A', new Namespaze(['B', 'C'])));
    }

    public function testReduceWithDepthZero()
    {
        $this->assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceToDepth(0)
        );
    }

    public function testReduceToDepthTwoWithoutNamespacesProducesClass()
    {
        $this->assertEquals(new Clazz('A'), (new Clazz('A'))->reduceToDepth(2));
    }

    public function testReduceDepthToTwoProducesTopTwoNamespaces()
    {
        $clazz = H::clazz('A\\B\\C\\D');
        $this->assertEquals(
            new Namespaze(['A', 'B']),
            $clazz->reduceToDepth(2)
        );
    }

    public function testReduceToDepthOfOneProducesOneNamespace()
    {
        $this->assertEquals(
            new Namespaze(['A']),
            H::clazz('A\\B\\C\\D')->reduceToDepth(1)
        );
    }

    public function testLeftReduceClassWithNamespace()
    {
        $this->assertEquals(
            H::clazz('D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(3)
        );
    }

    public function testCannotLeftReduceClassWithNamespaceByItsLength()
    {
        $this->assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(4)
        );
    }

    public function testCannotLeftReduceClassWithNamespaceByMoreThanItsLength()
    {
        $this->assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(5)
        );
    }
}
