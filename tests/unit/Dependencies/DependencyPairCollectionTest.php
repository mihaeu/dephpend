<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencyPairCollection
 * @covers Mihaeu\PhpDependencies\Util\AbstractCollection
 */
class DependencyPairCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsTrueIfAnyMatches()
    {
        $to = DependencyHelper::dependencySet('To, ToAnother');
        $dependencies = (new DependencyPairCollection())->add(new DependencyPair(new Clazz('Test'), $to));
        $this->assertTrue($dependencies->any(function (DependencyPair $dependency) use ($to) {
            return $dependency->to()->equals($to);
        }));
    }

    public function testReturnsFalseIfNoneMatches()
    {
        $dependencies = (new DependencyPairCollection())->add(
            new DependencyPair(new Clazz('Test'), DependencyHelper::dependencySet('To, ToAnother'))
        );
        $this->assertFalse($dependencies->any(function (DependencyPair $dependency) {
            return $dependency->from() === new Clazz('Other');
        }));
    }

    public function testEach()
    {
        $pair = DependencyHelper::dependencyPair('From --> To, ToAnother');
        $dependencies = (new DependencyPairCollection())
            ->add($pair);
        $dependencies->each(function (DependencyPair $dependency) use ($pair) {
            $this->assertEquals($pair, $dependency);
        });
    }

    public function testUniqueRemovesDuplicates()
    {
        $dependencies = DependencyHelper::convert('From --> To, To');
        $this->assertCount(1, $dependencies->unique());
    }

    public function testReduce()
    {
        $dependencies = DependencyHelper::convert('From --> To, ToAnother');
        $this->assertEquals('To'.PHP_EOL.'ToAnother', $dependencies->reduce('', function (string $output, DependencyPair $dependency) {
            return $output.$dependency->to()->toString();
        }));
    }

    public function testFromClasses()
    {
        $dependencies = DependencyHelper::convert('From --> To, ToAnother');
        $expected = (new DependencySet())
            ->add(new Clazz('From'));
        $this->assertEquals($expected, $dependencies->fromDependencies());
    }

    public function testAllClasses()
    {
        $dependencies = DependencyHelper::convert('From --> To, ToAnother');
        $expected = DependencyHelper::dependencySet('From, To, ToAnother');
        ;
        $this->assertEquals($expected, $dependencies->allDependencies());
    }

    public function testRemovesInternals()
    {
        $dependencies = DependencyHelper::convert('From --> To, SplFileInfo');
        $expected = (new DependencyPairCollection())
            ->add(DependencyHelper::dependencyPair('From --> To'));
        $this->assertEquals($expected, $dependencies->removeInternals());
    }

    public function testFilterByDepthOne()
    {
        $dependencies = DependencyHelper::convert('
            From --> A\\a\\To
            B\\b\\FromOther --> SplFileInfo
        ');
        $expected = DependencyHelper::convert('
            From --> _A
            _B --> SplFileInfo
        ');
        $actual = $dependencies->filterByDepth(1);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByDepthThree()
    {
        $dependencies = DependencyHelper::convert('
            VendorA\\ProjectA\\PathA\\From --> VendorB\\ProjectB\\PathB\\To
        ');
        $expected = DependencyHelper::convert('_VendorA\\ProjectA\\PathA --> _VendorB\\ProjectB\\PathB');
        $actual = $dependencies->filterByDepth(3);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByVendor()
    {
        $dependencies = DependencyHelper::convert('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $expected = DependencyHelper::convert('
            A --> C
        ');
        $this->assertEquals($expected, $dependencies->filterByNamespace('VendorA'));
    }

    public function testFilterByDepth0ReturnsEqual()
    {
        $dependencies = DependencyHelper::convert('
            VendorA\\A --> VendorB\\A
            VendorA\\A --> VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $this->assertEquals($dependencies, $dependencies->filterByDepth(0));
    }
    public function testRemoveClasses()
    {
        $expected = DependencyHelper::convert('
            _VendorA --> _VendorB
            _VendorB --> _VendorA
            _VendorC --> _');
        $actual = DependencyHelper::convert('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> B
        ')->filterClasses();
        $this->assertEquals($expected, $actual);
    }
}
