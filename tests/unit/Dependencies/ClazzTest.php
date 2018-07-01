<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper as H;
use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\Clazz
 * @covers Mihaeu\PhpDependencies\Dependencies\ClazzLike
 */
class ClazzTest extends \PHPUnit\Framework\TestCase
{
    public function testAcceptsUtf8Name()
    {
        assertEquals('á', (new Clazz('á'))->toString());
    }

    public function testHasValue()
    {
        assertEquals('Name', new Clazz('Name'));
        assertEquals('Name', (new Clazz('Name'))->toString());
    }

    public function testNamespace()
    {
        assertEquals(new Namespaze(['A', 'a']), (new Clazz('Name', new Namespaze(['A', 'a'])))->namespaze());
    }

    public function testToStringWithNamespace()
    {
        assertEquals('A\\a\\ClassA', (new Clazz('ClassA', new Namespaze(['A', 'a']))));
    }

    public function testEquals()
    {
        assertTrue((new Clazz('A'))->equals(new Clazz('A')));
    }

    public function testEqualsIgnoresType()
    {
        assertTrue((new Clazz('A'))->equals(new Interfaze('A')));
    }

    public function testDetectsIfClassHasNamespace()
    {
        assertTrue((new Clazz('Class', new Namespaze(['A'])))->hasNamespace());
    }

    public function testDetectsIfClassHasNoNamespace()
    {
        assertFalse((new Clazz('Class'))->hasNamespace());
    }

    public function testDepthWithoutNamespaceIsOne()
    {
        assertCount(1, new Clazz('A'));
    }

    public function testDepthWithNamespace()
    {
        assertCount(3, new Clazz('A', new Namespaze(['B', 'C'])));
    }

    public function testReduceWithDepthZero()
    {
        assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceToDepth(0)
        );
    }

    public function testReduceToDepthTwoWithoutNamespacesProducesClass()
    {
        assertEquals(new Clazz('A'), (new Clazz('A'))->reduceToDepth(2));
    }

    public function testReduceDepthToTwoProducesTopTwoNamespaces()
    {
        $clazz = H::clazz('A\\B\\C\\D');
        assertEquals(
            new Namespaze(['A', 'B']),
            $clazz->reduceToDepth(2)
        );
    }

    public function testReduceToDepthOfOneProducesOneNamespace()
    {
        assertEquals(
            new Namespaze(['A']),
            H::clazz('A\\B\\C\\D')->reduceToDepth(1)
        );
    }

    public function testLeftReduceClassWithNamespace()
    {
        assertEquals(
            H::clazz('D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(3)
        );
    }

    public function testCannotLeftReduceClassWithNamespaceByItsLength()
    {
        assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(4)
        );
    }

    /**
     * @see https://github.com/mihaeu/dephpend/issues/22
     */
    public function testAcceptsNumbersAsFirstCharacterInName()
    {
        assertEquals('Vendor\\1Sub\\2Factor', new Clazz('2Factor', new Namespaze(['Vendor', '1Sub'])));
    }

    public function testCannotLeftReduceClassWithNamespaceByMoreThanItsLength()
    {
        assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(5)
        );
    }

    public function testDetectsIfInOtherNamespace()
    {
        assertTrue(DependencyHelper::clazz('A\\b\\T\\Test')->inNamespaze(DependencyHelper::namespaze('A\\b')));
        assertTrue(DependencyHelper::clazz('A\\Test')->inNamespaze(DependencyHelper::namespaze('A')));
        assertTrue(DependencyHelper::clazz('Mihaeu\\PhpDependencies\\Util\\Collection')->inNamespaze(new Namespaze(['Mihaeu', 'PhpDependencies'])));
    }

    public function testDetectsIfNotInOtherNamespace()
    {
        assertFalse(DependencyHelper::clazz('Global')->inNamespaze(DependencyHelper::namespaze('A\\b\\T')));
    }

    public function testThrowsExceptionIfNameNotValid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name "Mihaeu\Test" is not valid.');
        new Clazz('Mihaeu\\Test');
    }

    public function testDetectsIfClassIsNotNamespaced()
    {
        assertFalse((new Clazz('NoNamespace'))->isNamespaced());
    }

    public function testDetectsIfClassIsNamespaced()
    {
        assertTrue((new Clazz('HasNamespace', new Namespaze(['Vendor'])))->isNamespaced());
    }
}
