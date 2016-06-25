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
        $dependingClasses = $dependencyCollection->findClassesDependingOn(new Clazz('From'))->toArray();
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

    public function testAllClasses()
    {
        $dependencyCollection = (new DependencyCollection())
            ->add(new Dependency(new Clazz('From'), new Clazz('To')))
            ->add(new Dependency(new Clazz('From'), new Clazz('ToAnother')));
        $expected = (new ClazzCollection())
            ->add(new Clazz('From'))
            ->add(new Clazz('To'))
            ->add(new Clazz('ToAnother'));
        $this->assertEquals($expected, $dependencyCollection->allClasses());
    }

    public function testRemovesInternals()
    {
        $dependencyCollection = (new DependencyCollection())
            ->add(new Dependency(new Clazz('From'), new Clazz('To')))
            ->add(new Dependency(new Clazz('From'), new Clazz('SplFileInfo')));
        $expected = (new DependencyCollection())
            ->add(new Dependency(new Clazz('From'), new Clazz('To')));
        $this->assertEquals($expected, $dependencyCollection->removeInternals());
    }

    public function testNamespacesOnly()
    {
        $dependencyCollection = (new DependencyCollection())
            ->add(new Dependency(new Clazz('NamespaceA.From'), new Clazz('NamespaceB.Sub.To')))
            ->add(new Dependency(new Clazz('NamespaceB.From'), new Clazz('ToAnother')))
            ->add(new Dependency(new Clazz('NamespaceB.From'), new Clazz('NamespaceB.ToAnother')));
        $namespacesOnly = $dependencyCollection->onlyNamespaces();
        $this->assertEquals(new Dependency(new Clazz('NamespaceA'), new Clazz('NamespaceB.Sub')), $namespacesOnly->toArray()[0]);
        $this->assertCount(1, $namespacesOnly);
    }
}
