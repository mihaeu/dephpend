<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use InvalidArgumentException;
use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\DependencyHelper as H;
use Mihaeu\PhpDependencies\Util\Collection;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\Clazz
 * @covers Mihaeu\PhpDependencies\Dependencies\ClazzLike
 */
class ClazzTest extends TestCase
{
    public function testAcceptsUtf8Name(): void
    {
        assertEquals('á', (new Clazz('á'))->toString());
    }

    public function testHasValue(): void
    {
        assertEquals('Name', new Clazz('Name'));
        assertEquals('Name', (new Clazz('Name'))->toString());
    }

    public function testNamespace(): void
    {
        assertEquals(new Namespaze(['A', 'a']), (new Clazz('Name', new Namespaze(['A', 'a'])))->namespaze());
    }

    public function testToStringWithNamespace(): void
    {
        assertEquals('A\\a\\ClassA', new Clazz('ClassA', new Namespaze(['A', 'a'])));
    }

    public function testEquals(): void
    {
        assertTrue((new Clazz('A'))->equals(new Clazz('A')));
    }

    public function testEqualsIgnoresType(): void
    {
        assertTrue((new Clazz('A'))->equals(new Interfaze('A')));
    }

    public function testDetectsIfClassHasNamespace(): void
    {
        assertTrue((new Clazz('Class', new Namespaze(['A'])))->hasNamespace());
    }

    public function testDetectsIfClassHasNoNamespace(): void
    {
        assertFalse((new Clazz('Class'))->hasNamespace());
    }

    public function testDepthWithoutNamespaceIsOne(): void
    {
        assertCount(1, new Clazz('A'));
    }

    public function testDepthWithNamespace(): void
    {
        assertCount(3, new Clazz('A', new Namespaze(['B', 'C'])));
    }

    public function testReduceWithDepthZero(): void
    {
        assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceToDepth(0)
        );
    }

    public function testReduceToDepthTwoWithoutNamespacesProducesClass(): void
    {
        assertEquals(new Clazz('A'), (new Clazz('A'))->reduceToDepth(2));
    }

    public function testReduceDepthToTwoProducesTopTwoNamespaces(): void
    {
        $clazz = H::clazz('A\\B\\C\\D');
        assertEquals(
            new Namespaze(['A', 'B']),
            $clazz->reduceToDepth(2)
        );
    }

    public function testReduceToDepthOfOneProducesOneNamespace(): void
    {
        assertEquals(
            new Namespaze(['A']),
            H::clazz('A\\B\\C\\D')->reduceToDepth(1)
        );
    }

    public function testLeftReduceClassWithNamespace(): void
    {
        assertEquals(
            H::clazz('D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(3)
        );
    }

    public function testCannotLeftReduceClassWithNamespaceByItsLength(): void
    {
        assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(4)
        );
    }

    /**
     * @see https://github.com/mihaeu/dephpend/issues/22
     */
    public function testAcceptsNumbersAsFirstCharacterInName(): void
    {
        assertEquals('Vendor\\1Sub\\2Factor', new Clazz('2Factor', new Namespaze(['Vendor', '1Sub'])));
    }

    public function testCannotLeftReduceClassWithNamespaceByMoreThanItsLength(): void
    {
        assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(5)
        );
    }

    public function testDetectsIfInOtherNamespace(): void
    {
        assertTrue(DependencyHelper::clazz('A\\b\\T\\Test')->inNamespaze(DependencyHelper::namespaze('A\\b')));
        assertTrue(DependencyHelper::clazz('A\\Test')->inNamespaze(DependencyHelper::namespaze('A')));
        assertTrue(DependencyHelper::clazz(Collection::class)->inNamespaze(new Namespaze(['Mihaeu', 'PhpDependencies'])));
    }

    public function testDetectsIfNotInOtherNamespace(): void
    {
        assertFalse(DependencyHelper::clazz('Global')->inNamespaze(DependencyHelper::namespaze('A\\b\\T')));
    }

    public function testThrowsExceptionIfNameNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name "Mihaeu\Test" is not valid.');
        new Clazz('Mihaeu\\Test');
    }

    public function testDetectsIfClassIsNotNamespaced(): void
    {
        assertFalse((new Clazz('NoNamespace'))->isNamespaced());
    }

    public function testDetectsIfClassIsNamespaced(): void
    {
        assertTrue((new Clazz('HasNamespace', new Namespaze(['Vendor'])))->isNamespaced());
    }
}
