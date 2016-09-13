<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencyMap
 * @covers Mihaeu\PhpDependencies\Util\AbstractMap
 */
class DependencyMapTest extends \PHPUnit_Framework_TestCase
{
    public function testNoDuplicates()
    {
        $set = DependencyHelper::convert('
            A --> B
            B --> C
        ');
        $this->assertEquals($set, $set->add(new Clazz('A'), new Clazz('B')));
    }

    public function testReturnsTrueIfAnyMatches()
    {
        $to = DependencyHelper::dependencySet('To, ToAnother');
        $dependencies = (new DependencyMap())->addSet(new Clazz('Test'), $to);
        $this->assertTrue($dependencies->any(function (DependencySet $toDependencies, Dependency $fromDependency) use ($to) {
            return $toDependencies->equals($to);
        }));
    }

    public function testReturnsTrueIfNoneMatches()
    {
        $dependencies = (new DependencyMap())->addSet(new Clazz('Test'), DependencyHelper::dependencySet('To, ToAnother'));
        $this->assertTrue($dependencies->none(function (DependencySet $toDependencies, Dependency $fromDependency) {
            return $fromDependency === new Clazz('Other');
        }));
    }

    public function testEach()
    {
        $to = DependencyHelper::dependencySet('To, ToAnother');
        $dependencies = (new DependencyMap())->addSet(new Clazz('From'), $to);
        $dependencies->each(function (DependencySet $toDependencies, Dependency $fromDependency) use ($to) {
            $this->assertEquals($toDependencies, $to);
        });
    }

    public function testReduce()
    {
        $dependencies = DependencyHelper::convert('From --> To, ToAnother');
        $this->assertEquals('To'.PHP_EOL.'ToAnother', $dependencies->reduce('', function (string $output, DependencySet $toDependencies, Dependency $fromDependency) {
            return $output.$toDependencies->toString();
        }));
    }

    public function testFromClasses()
    {
        $dependencies = DependencyHelper::convert('From --> To, ToAnother');
        $expected = (new DependencySet())->add(new Clazz('From'));
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
        $expected = (new DependencyMap())->add(new Clazz('From'), new Clazz('To'));
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

    public function testToString()
    {
        $this->assertEquals(
            'VendorA\\A --> VendorB\\A'.PHP_EOL
            .'VendorA\\A --> VendorA\\C'.PHP_EOL
            .'VendorB\\B --> VendorA\\A'.PHP_EOL
            .'VendorC\\C --> B', DependencyHelper::convert('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> B
        ')->toString());
    }
}
