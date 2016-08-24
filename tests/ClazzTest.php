<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\Clazz
 */
class ClazzTest extends \PHPUnit_Framework_TestCase
{
    public function testHasValue()
    {
        $this->assertEquals('Name', new Clazz('Name'));
        $this->assertEquals('Name', (new Clazz('Name'))->toString());
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
        $this->assertEquals(1, (new Clazz('A'))->depth());
    }

    public function testDepthWithNamespace()
    {
        $this->assertEquals(3, (new Clazz('A', new Namespaze(['B', 'C'])))->depth());
    }

    public function testReduceWithDepthZero()
    {
        $this->assertEquals(
            new Clazz('A', new Namespaze(['B', 'C', 'D'])),
            (new Clazz('A', new Namespaze(['B', 'C', 'D'])))->reduceToDepth(0)
        );
    }

    public function testReduceToDepthTwoWithoutNamespacesProducesClass()
    {
        $this->assertEquals(new Clazz('A'), (new Clazz('A'))->reduceToDepth(2));
    }

    public function testReduceDepthToTwoProducesTopTwoNamespaces()
    {
        $this->assertEquals(
            new Namespaze(['B', 'C']),
            (new Clazz('A', new Namespaze(['B', 'C', 'D'])))->reduceToDepth(2)
        );
    }

    public function testReduceToDepthOfOneProducesOneNamespace()
    {
        $this->assertEquals(
            new Namespaze(['B']),
            (new Clazz('A', new Namespaze(['B', 'C', 'D'])))->reduceToDepth(1)
        );
    }
}
