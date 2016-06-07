<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyCollection
 * @covers Mihaeu\PhpDependencies\AbstractCollection
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\ClazzCollection
 * @uses Mihaeu\PhpDependencies\Dependency
 */
class DependencyCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testEach()
    {
        $dependencyCollection = (new DependencyCollection())
            ->add(new Dependency(new Clazz('From'), new Clazz('To')));
        $dependencyCollection->each(function (Dependency $dependency) {
            $this->assertEquals(new Dependency(new Clazz('From'), new Clazz('To')), $dependency);
        });
    }

    public function testDoesNotAddDuplicated()
    {
        $dependencyCollection = (new DependencyCollection())
            ->add(new Dependency(new Clazz('From'), new Clazz('To')))
            ->add(new Dependency(new Clazz('From'), new Clazz('To')));
        $this->assertCount(1, $dependencyCollection);
    }

    public function testFindsClassesDependingOnClass()
    {
        $dependencyCollection = (new DependencyCollection())
            ->add(new Dependency(new Clazz('From'), new Clazz('To')))
            ->add(new Dependency(new Clazz('From'), new Clazz('ToAnother')));
        $dependingClasses = $dependencyCollection->classesDependingOn(new Clazz('From'))->toArray();
        $this->assertEquals(new Clazz('To'), $dependingClasses[0]);
        $this->assertEquals(new Clazz('ToAnother'), $dependingClasses[1]);
    }

    public function testReduce()
    {
        $dependencyCollection = (new DependencyCollection())
            ->add(new Dependency(new Clazz('From'), new Clazz('To')))
            ->add(new Dependency(new Clazz('From'), new Clazz('ToAnother')));
        $this->assertEquals('ToToAnother', $dependencyCollection->reduce('', function (string $output, Dependency $dependency) {
            return $output.$dependency->to()->toString();
        }));
    }
}
