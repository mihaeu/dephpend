<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencySet
 * @covers Mihaeu\PhpDependencies\Util\AbstractCollection
 */
class DependencySetTest extends \PHPUnit\Framework\TestCase
{
    public function testAdd()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $clazzCollection->each(function (Dependency $clazz) {
            assertEquals(new Clazz('Test'), $clazz);
        });
    }

    public function testIsImmutable()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $newCollectionAfterRefusingDuplicate = $clazzCollection->add(new Clazz('Test'));
        assertNotSame($clazzCollection, $newCollectionAfterRefusingDuplicate);
    }

    public function testDoesNotAcceptDuplicates()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        assertEquals($clazzCollection, $clazzCollection->add(new Clazz('Test')));
    }

    public function testToArray()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        assertEquals([new Clazz('Test')], $clazzCollection->toArray());
    }

    public function testToString()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'))
            ->add(new Clazz('Test2'));
        assertEquals('Test'.PHP_EOL.'Test2', $clazzCollection->__toString());
    }

    public function testFilter()
    {
        $expected = DependencyHelper::dependencySet('AB, AC');
        assertEquals($expected, DependencyHelper::dependencySet('AB, AC, BA, CA')->filter(function (Dependency $dependency) {
            return strpos($dependency->toString(), 'A') === 0;
        }));
    }

    public function testReduce()
    {
        assertEquals('ABC', DependencyHelper::dependencySet('A, B, C')->reduce('', function (string $carry, Dependency $dependency) {
            return $carry.$dependency->toString();
        }));
    }

    public function testNoneIsTrueWhenNoneMatches()
    {
        assertTrue(DependencyHelper::dependencySet('AB, AC, BA, CA')->none(function (Dependency $dependency) {
            return strpos($dependency->toString(), 'D') === 0;
        }));
    }

    public function testNoneIsFalseWhenSomeMatch()
    {
        assertFalse(DependencyHelper::dependencySet('AB, AC, BA, CA')->none(function (Dependency $dependency) {
            return strpos($dependency->toString(), 'A') === 0;
        }));
    }
}
