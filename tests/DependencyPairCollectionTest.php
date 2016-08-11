<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyCollection
 * @covers Mihaeu\PhpDependencies\AbstractCollection
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\ClazzCollection
 * @uses Mihaeu\PhpDependencies\DependencyPair
 */
class DependencyPairCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testEach()
    {
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')));
        $dependencyCollection->each(function (DependencyPair $dependency) {
            $this->assertEquals(new DependencyPair(new Clazz('From'), new Clazz('To')), $dependency);
        });
    }

    public function testDoesNotAddDuplicated()
    {
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')));
        $this->assertCount(1, $dependencyCollection);
    }

    public function testFindsClassesDependingOnClass()
    {
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('ToAnother')));
        $dependingClasses = $dependencyCollection->findClassesDependingOn(new Clazz('From'))->toArray();
        $this->assertEquals(new Clazz('To'), $dependingClasses[0]);
        $this->assertEquals(new Clazz('ToAnother'), $dependingClasses[1]);
    }

    public function testReduce()
    {
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('ToAnother')));
        $this->assertEquals('ToToAnother', $dependencyCollection->reduce('', function (string $output, DependencyPair $dependency) {
            return $output.$dependency->to()->toString();
        }));
    }

    public function testAllClasses()
    {
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('ToAnother')));
        $expected = (new ClazzCollection())
            ->add(new Clazz('From'))
            ->add(new Clazz('To'))
            ->add(new Clazz('ToAnother'));
        $this->assertEquals($expected, $dependencyCollection->allClasses());
    }

    public function testRemovesInternals()
    {
        $dependencyCollection = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')))
            ->add(new DependencyPair(new Clazz('From'), new Clazz('SplFileInfo')));
        $expected = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('From'), new Clazz('To')));
        $this->assertEquals($expected, $dependencyCollection->removeInternals());
    }
}
