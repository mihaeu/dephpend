<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencySet
 * @covers Mihaeu\PhpDependencies\Util\AbstractCollection
 */
class DependencySetTest extends TestCase
{
    public function testAdd(): void
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $clazzCollection->each(function (Dependency $clazz) {
            $this->assertEquals(new Clazz('Test'), $clazz);
        });
    }

    public function testIsImmutable(): void
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $newCollectionAfterRefusingDuplicate = $clazzCollection->add(new Clazz('Test'));
        $this->assertNotSame($clazzCollection, $newCollectionAfterRefusingDuplicate);
    }

    public function testDoesNotAcceptDuplicates(): void
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $this->assertEquals($clazzCollection, $clazzCollection->add(new Clazz('Test')));
    }

    public function testToArray(): void
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $this->assertEquals([new Clazz('Test')], $clazzCollection->toArray());
    }

    public function testToString(): void
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'))
            ->add(new Clazz('Test2'));
        $this->assertEquals('Test'.PHP_EOL.'Test2', $clazzCollection->__toString());
    }

    public function testFilter(): void
    {
        $expected = DependencyHelper::dependencySet('AB, AC');
        $this->assertEquals($expected, DependencyHelper::dependencySet('AB, AC, BA, CA')->filter(function (Dependency $dependency) {
            return strpos($dependency->toString(), 'A') === 0;
        }));
    }

    public function testReduce(): void
    {
        $this->assertEquals('ABC', DependencyHelper::dependencySet('A, B, C')->reduce('', function (string $carry, Dependency $dependency) {
            return $carry.$dependency->toString();
        }));
    }

    public function testNoneIsTrueWhenNoneMatches(): void
    {
        $this->assertTrue(DependencyHelper::dependencySet('AB, AC, BA, CA')->none(function (Dependency $dependency) {
            return strpos($dependency->toString(), 'D') === 0;
        }));
    }

    public function testNoneIsFalseWhenSomeMatch(): void
    {
        $this->assertFalse(DependencyHelper::dependencySet('AB, AC, BA, CA')->none(function (Dependency $dependency) {
            return strpos($dependency->toString(), 'A') === 0;
        }));
    }
}
