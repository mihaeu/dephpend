<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Dependencies\DependencyMap::class)]
#[CoversClass(\Mihaeu\PhpDependencies\Util\AbstractMap::class)]
class DependencyMapTest extends TestCase
{
    public function testNoDuplicates(): void
    {
        $map = DependencyHelper::map('
            A --> B
            B --> C
        ');
        $this->assertEquals($map, $map->add(new Clazz('A'), new Clazz('B')));
    }

    public function testAddMapToMap(): void
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

    public function testAddMoreDependenciesToExistingPair(): void
    {
        $map = DependencyHelper::map('A --> B');
        $this->assertEquals(
            DependencyHelper::map('A --> B, C, D'),
            $map->addSet(new Clazz('A'), DependencyHelper::dependencySet('C, D'))
        );
    }

    public function testDoesNotAcceptDependenciesMappingToThemselves(): void
    {
        $this->assertCount(0, DependencyHelper::map('')->add(new Clazz('A'), new Clazz('A')));
    }

    public function testReturnsTrueIfAnyMatches(): void
    {
        $toSet = DependencyHelper::dependencySet('To, ToAnother');
        $dependencies = (new DependencyMap())->addSet(new Clazz('Test'), $toSet);
        $this->assertTrue($dependencies->any(function (Dependency $from, Dependency $to) use ($toSet) {
            return $toSet->contains($to);
        }));
    }

    public function testReturnsTrueIfNoneMatches(): void
    {
        $dependencies = (new DependencyMap())->addSet(new Clazz('Test'), DependencyHelper::dependencySet('To, ToAnother'));
        $this->assertTrue($dependencies->none(function (Dependency $from, Dependency $to) {
            return $from === new Clazz('Other');
        }));
    }

    public function testEach(): void
    {
        DependencyHelper::map('From --> To, ToAnother')->each(function (Dependency $from, Dependency $to) {
            $this->assertTrue($from->equals(new Clazz('From')));
        });
    }

    public function testReduce(): void
    {
        $dependencies = DependencyHelper::map('From --> To, ToAnother');
        $this->assertEquals('ToToAnother', $dependencies->reduce('', function (string $output, Dependency $from, Dependency $to) {
            return $output.$to->toString();
        }));
    }

    public function testFromClasses(): void
    {
        $dependencies = DependencyHelper::map('From --> To, ToAnother');
        $expected = (new DependencySet())->add(new Clazz('From'));
        $this->assertEquals($expected, $dependencies->fromDependencies());
    }

    public function testAllClasses(): void
    {
        $dependencies = DependencyHelper::map('From --> To, ToAnother');
        $expected = DependencyHelper::dependencySet('From, To, ToAnother');
        $this->assertEquals($expected, $dependencies->allDependencies());
    }

    public function testToString(): void
    {
        $this->assertEquals(
            'VendorA\\A --> VendorB\\A'.PHP_EOL
            .'VendorA\\A --> VendorA\\C'.PHP_EOL
            .'VendorB\\B --> VendorA\\A'.PHP_EOL
            .'VendorC\\C --> B',
            DependencyHelper::map('
            VendorA\\A --> VendorB\\A, VendorA\\C
            VendorB\\B --> VendorA\\A
            VendorC\\C --> B
        ')->toString()
        );
    }

    public function testMapToArray(): void
    {
        $this->assertEquals([new Clazz('A'), new Clazz('C')], DependencyHelper::map('
            A --> B
            C --> D
        ')->mapToArray(function (Dependency $from, Dependency $to) {
            return $from;
        }));
    }

    public function testToArray(): void
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

    public function testFilter(): void
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

    public function testCount(): void
    {
        $this->assertCount(2, DependencyHelper::map('
            A --> B, C, D
            D --> E
        '));
    }

    public function testEquals(): void
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

    public function testContainsIsTrueIfItMatchesTheKey(): void
    {
        $this->assertTrue(DependencyHelper::map('
            A --> B, C, D
            D --> E
        ')->contains(new Clazz('A')));
    }

    public function testContainsIsFalseIfItOnlyMatchesTheValue(): void
    {
        $this->assertFalse(DependencyHelper::map('
            A --> B, C, D
            D --> E
        ')->contains(new Clazz('E')));
    }

    public function testMapAllDependencies(): void
    {
        $map = DependencyHelper::map('
            A\b --> C
            Y --> Z\d
        ');
        $this->assertEquals(DependencyHelper::dependencySet('_A, _Z'), $map->mapAllDependencies(function (Dependency $dependency) {
            return $dependency->namespaze();
        }));
    }

    public function testGet(): void
    {
        $this->assertEquals(
            DependencyHelper::dependencySet('A, B, C'),
            DependencyHelper::map('D --> A, B, C')->get(new Clazz('D'))
        );
    }

    public function testReduceEachDependency(): void
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

    public function testDoesNotPrintNullDependenciesInKey(): void
    {
        $map = (new DependencyMap())->add(new NullDependency(), new Clazz('A'));
        $this->assertEmpty($map->toString());
    }

    public function testDoesNotPrintNullDependenciesInValue(): void
    {
        $map = (new DependencyMap())->add(new Clazz('A'), new NullDependency());
        $this->assertEmpty($map->toString());
    }

    public function testCannotAddEmptyNamespaceAsFrom(): void
    {
        $this->assertEmpty((new DependencyMap())->add(new Clazz('A'), new Namespaze([])));
    }

    public function testCannotAddEmptyNamespaceAsTo(): void
    {
        $this->assertEmpty((new DependencyMap())->add(new Namespaze([]), new Clazz('A')));
    }

    public function testCannotAddSelf(): void
    {
        $this->assertEmpty((new DependencyMap())->add(new Clazz('A'), new Clazz('self')));
    }

    public function testCannotAddDependencyToYourself(): void
    {
        $dependencyMap = (new DependencyMap())
            ->add(
                new Clazz('Collection', new Namespaze(['Mihaeu', 'PhpDependencies', 'Util'])),
                new Interfaze('Collection', new Namespaze(['Mihaeu', 'PhpDependencies', 'Util']))
            )
            ->add(
                new Clazz('A'),
                new Clazz('static')
            );
        $this->assertEmpty($dependencyMap);
    }
}
