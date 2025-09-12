<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use InvalidArgumentException;
use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\DependencyHelper as H;
use Mihaeu\PhpDependencies\Util\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Dependencies\Clazz::class)]
#[CoversClass(\Mihaeu\PhpDependencies\Dependencies\ClazzLike::class)]
class ClazzTest extends TestCase
{
    public function testAcceptsUtf8Name(): void
    {
        $this->assertEquals('á', (new Clazz('á'))->toString());
    }

    public function testHasValue(): void
    {
        $this->assertEquals('Name', new Clazz('Name'));
        $this->assertEquals('Name', (new Clazz('Name'))->toString());
    }

    public function testNamespace(): void
    {
        $this->assertEquals(new Namespaze(['A', 'a']), (new Clazz('Name', new Namespaze(['A', 'a'])))->namespaze());
    }

    public function testToStringWithNamespace(): void
    {
        $this->assertEquals('A\\a\\ClassA', new Clazz('ClassA', new Namespaze(['A', 'a'])));
    }

    public function testEquals(): void
    {
        $this->assertTrue((new Clazz('A'))->equals(new Clazz('A')));
    }

    public function testEqualsIgnoresType(): void
    {
        $this->assertTrue((new Clazz('A'))->equals(new Interfaze('A')));
    }

    public function testDetectsIfClassHasNamespace(): void
    {
        $this->assertTrue((new Clazz('Class', new Namespaze(['A'])))->hasNamespace());
    }

    public function testDetectsIfClassHasNoNamespace(): void
    {
        $this->assertFalse((new Clazz('Class'))->hasNamespace());
    }

    public function testDepthWithoutNamespaceIsOne(): void
    {
        $this->assertCount(1, new Clazz('A'));
    }

    public function testDepthWithNamespace(): void
    {
        $this->assertCount(3, new Clazz('A', new Namespaze(['B', 'C'])));
    }

    public function testReduceWithDepthZero(): void
    {
        $this->assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceToDepth(0)
        );
    }

    public function testReduceToDepthTwoWithoutNamespacesProducesClass(): void
    {
        $this->assertEquals(new Clazz('A'), (new Clazz('A'))->reduceToDepth(2));
    }

    public function testReduceDepthToTwoProducesTopTwoNamespaces(): void
    {
        $clazz = H::clazz('A\\B\\C\\D');
        $this->assertEquals(
            new Namespaze(['A', 'B']),
            $clazz->reduceToDepth(2)
        );
    }

    public function testReduceToDepthOfOneProducesOneNamespace(): void
    {
        $this->assertEquals(
            new Namespaze(['A']),
            H::clazz('A\\B\\C\\D')->reduceToDepth(1)
        );
    }

    public function testLeftReduceClassWithNamespace(): void
    {
        $this->assertEquals(
            H::clazz('D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(3)
        );
    }

    public function testCannotLeftReduceClassWithNamespaceByItsLength(): void
    {
        $this->assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(4)
        );
    }

    /**
     * @see https://github.com/mihaeu/dephpend/issues/22
     */
    public function testAcceptsNumbersAsFirstCharacterInName(): void
    {
        $this->assertEquals('Vendor\\1Sub\\2Factor', new Clazz('2Factor', new Namespaze(['Vendor', '1Sub'])));
    }

    public function testCannotLeftReduceClassWithNamespaceByMoreThanItsLength(): void
    {
        $this->assertEquals(
            H::clazz('A\\B\\C\\D'),
            H::clazz('A\\B\\C\\D')->reduceDepthFromLeftBy(5)
        );
    }

    public function testDetectsIfInOtherNamespace(): void
    {
        $this->assertTrue(DependencyHelper::clazz('A\\b\\T\\Test')->inNamespaze(DependencyHelper::namespaze('A\\b')));
        $this->assertTrue(DependencyHelper::clazz('A\\Test')->inNamespaze(DependencyHelper::namespaze('A')));
        $this->assertTrue(DependencyHelper::clazz(Collection::class)->inNamespaze(new Namespaze(['Mihaeu', 'PhpDependencies'])));
    }

    public function testDetectsIfNotInOtherNamespace(): void
    {
        $this->assertFalse(DependencyHelper::clazz('Global')->inNamespaze(DependencyHelper::namespaze('A\\b\\T')));
    }

    public function testThrowsExceptionIfNameNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name "Mihaeu\Test" is not valid.');
        new Clazz('Mihaeu\\Test');
    }

    public function testDetectsIfClassIsNotNamespaced(): void
    {
        $this->assertFalse((new Clazz('NoNamespace'))->isNamespaced());
    }

    public function testDetectsIfClassIsNamespaced(): void
    {
        $this->assertTrue((new Clazz('HasNamespace', new Namespaze(['Vendor'])))->isNamespaced());
    }
}
