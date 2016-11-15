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
        $map = DependencyHelper::map('
            A --> B
            B --> C
        ');
        $this->assertEquals($map, $map->add(new Clazz('A'), new Clazz('B')));
    }

    public function testAddMapToMap()
    {
        $this->assertEquals(DependencyHelper::map('
            A --> B, C
            B --> C
        '), DependencyHelper::map('
            A --> B    
        ')->addMap(DependencyHelper::map('
            A --> B, C
            B --> C
        ')));
    }

    public function testAddMoreDependenciesToExistingPair()
    {
        $map = DependencyHelper::map('A --> B');
        $this->assertEquals(
            DependencyHelper::map('A --> B, C, D'),
            $map->addSet(new Clazz('A'), DependencyHelper::dependencySet('C, D'))
        );
    }

    public function testDoesNotAcceptDependenciesMappingToThemselves()
    {
        $this->assertCount(0, DependencyHelper::map('')->add(new Clazz('A'), new Clazz('A')));
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

    public function testMapAllDependencies()
    {
        $map = DependencyHelper::map('
            A\b --> C
            Y --> Z\d
        ');
        $this->assertEquals(DependencyHelper::dependencySet('_A, _Z'), $map->mapAllDependencies(function (Dependency $dependency) {
            return $dependency->namespaze();
        }));
    }

    public function testGet()
    {
        $this->assertEquals(
            DependencyHelper::dependencySet('A, B, C'),
            DependencyHelper::map('D --> A, B, C')->get(new Clazz('D'))
        );
    }

    public function testReduceEachDependency()
    {
        $this->assertEquals(DependencyHelper::map('
            _A --> _B, _C
        '), DependencyHelper::map('
            A\b --> B\d, C\d
            A\a --> A\b
        ')->reduceEachDependency(function (Dependency $dependency) {
            return $dependency->namespaze();
        }));
    }

    public function testDoesNotPrintNullDependenciesInKey()
    {
        $map = (new DependencyMap())->add(new NullDependency(), new Clazz('A'));
        $this->assertEmpty($map->toString());
    }

    public function testDoesNotPrintNullDependenciesInValue()
    {
        $map = (new DependencyMap())->add(new Clazz('A'), new NullDependency());
        $this->assertEmpty($map->toString());
    }

    public function testCannotAddEmptyNamespaceAsFrom()
    {
        $this->assertEmpty((new DependencyMap())->add(new Clazz('A'), new Namespaze([])));
    }

    public function testCannotAddEmptyNamespaceAsTo()
    {
        $this->assertEmpty((new DependencyMap())->add(new Namespaze([]), new Clazz('A')));
    }
}
