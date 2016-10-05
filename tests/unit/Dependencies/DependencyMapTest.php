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
        $set = DependencyHelper::map('
            A --> B
            B --> C
        ');
        $this->assertEquals($set, $set->add(new Clazz('A'), new Clazz('B')));
    }

    public function testReturnsTrueIfAnyMatches()
    {
        $toSet = DependencyHelper::dependencySet('To, ToAnother');
        $dependencies = (new DependencyMap())->addSet(new Clazz('Test'), $toSet);
        $this->assertTrue($dependencies->any(function (Dependency $from, Dependency $to) use ($toSet) {
            return $toSet->contains($to);
        }));
    }

    public function testReturnsTrueIfNoneMatches()
    {
        $dependencies = (new DependencyMap())->addSet(new Clazz('Test'), DependencyHelper::dependencySet('To, ToAnother'));
        $this->assertTrue($dependencies->none(function (Dependency $from, Dependency $to) {
            return $from === new Clazz('Other');
        }));
    }

    public function testEach()
    {
        DependencyHelper::map('From --> To, ToAnother')->each(function (Dependency $from, Dependency $to) {
            $this->assertTrue($from->equals(new Clazz('From')));
        });
    }

    public function testReduce()
    {
        $dependencies = DependencyHelper::map('From --> To, ToAnother');
        $this->assertEquals('ToToAnother', $dependencies->reduce('', function (string $output, Dependency $from, Dependency $to) {
            return $output.$to->toString();
        }));
    }

    public function testFromClasses()
    {
        $dependencies = DependencyHelper::map('From --> To, ToAnother');
        $expected = (new DependencySet())->add(new Clazz('From'));
        $this->assertEquals($expected, $dependencies->fromDependencies());
    }

    public function testAllClasses()
    {
        $dependencies = DependencyHelper::map('From --> To, ToAnother');
        $expected = DependencyHelper::dependencySet('From, To, ToAnother');
        ;
        $this->assertEquals($expected, $dependencies->allDependencies());
    }

    public function testRemovesInternals()
    {
        $dependencies = DependencyHelper::map('From --> To, SplFileInfo');
        $expected = (new DependencyMap())->add(new Clazz('From'), new Clazz('To'));
        $this->assertEquals($expected, $dependencies->removeInternals());
    }

    public function testFilterByDepthOne()
    {
        $dependencies = DependencyHelper::map('
            From --> A\\a\\To
            B\\b\\FromOther --> SplFileInfo
        ');
        $expected = DependencyHelper::map('
            From --> _A
            _B --> SplFileInfo
        ');
        $actual = $dependencies->filterByDepth(1);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByDepthThree()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\ProjectA\\PathA\\From --> VendorB\\ProjectB\\PathB\\To
        ');
        $expected = DependencyHelper::map('_VendorA\\ProjectA\\PathA --> _VendorB\\ProjectB\\PathB');
        $actual = $dependencies->filterByDepth(3);
        $this->assertEquals($expected, $actual);
    }

    public function testFilterByVendor()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $expected = DependencyHelper::map('
            A --> C
        ');
        $this->assertEquals($expected, $dependencies->filterByNamespace('VendorA'));
    }

    public function testFilterByDepth0ReturnsEqual()
    {
        $dependencies = DependencyHelper::map('
            VendorA\\A --> VendorB\\A
            VendorA\\A --> VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> VendorA\\A
        ');
        $this->assertEquals($dependencies, $dependencies->filterByDepth(0));
    }
    public function testRemoveClasses()
    {
        $expected = DependencyHelper::map('
            _VendorA --> _VendorB
            _VendorB --> _VendorA
        ');
        $actual = DependencyHelper::map('
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
            .'VendorC\\C --> B', DependencyHelper::map('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> B
        ')->toString());
    }

    public function testMapToArray()
    {
        $this->assertEquals([new Clazz('A'), new Clazz('C')], DependencyHelper::map('
            A --> B
            C --> D
        ')->mapToArray(function (Dependency $from, Dependency $to) {
            return $from;
        }));
    }

    public function testToArray()
    {
        $this->assertEquals([
            'A' => [
                'key'   => new Clazz('A'),
                'value' => (new DependencySet())->add(new Clazz('B')),
            ],
            'C' => [
                'key'   => new Clazz('C'),
                'value' => (new DependencySet())->add(new Clazz('D')),
            ],
        ], DependencyHelper::map('
            A --> B
            C --> D
        ')->toArray());
    }

    public function testFilter()
    {
        $this->assertEquals(
            DependencyHelper::map('A --> B'),
            DependencyHelper::map('
                A --> B
                C --> D
            ')->filter(function (Dependency $from, Dependency $to) {
                return $from->equals(new Clazz('A'));
            })
        );
    }

    public function testCount()
    {
        $this->assertCount(2, DependencyHelper::map('
            A --> B, C, D
            D --> E
        '));
    }

    public function testEquals()
    {
        $one = DependencyHelper::map('
            A --> B, C, D
            D --> E
        ');
        $two = DependencyHelper::map('
            A --> B, C, D
            D --> E
        ')->add(new Clazz('D'), new Clazz('E'));
        $this->assertTrue($one->equals($two));
    }

    public function testContainsIsTrueIfItMatchesTheKey()
    {
        $this->assertTrue(DependencyHelper::map('
            A --> B, C, D
            D --> E
        ')->contains(new Clazz('A')));
    }

    public function testContainsIsFalseIfItOnlyMatchesTheValue()
    {
        $this->assertFalse(DependencyHelper::map('
            A --> B, C, D
            D --> E
        ')->contains(new Clazz('E')));
    }

    public function testFilterFromDependencies()
    {
        $this->assertEquals(DependencyHelper::map('
            Good\\A --> Bad\\B
            Good\\B --> Good\\C
        '), DependencyHelper::map('
            Good\\A --> Bad\\B
            Good\\B --> Good\\C
            Bad\\B --> Good\\A
        ')->filterByFromNamespace('Good'));
    }
}
