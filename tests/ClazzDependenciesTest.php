<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\ClazzDependencies
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 * @uses Mihaeu\PhpDependencies\ClazzCollection
 * @uses Mihaeu\PhpDependencies\Dependency
 */
class ClazzDependenciesTest extends \PHPUnit_Framework_TestCase
{
    public function testEach()
    {
        $clazzDependencies = (new ClazzDependencies())
            ->addDependency(new Dependency(new Clazz('From'), new Clazz('To')));
        $clazzDependencies->each(function (Dependency $dependency) {
            $this->assertEquals(new Dependency(new Clazz('From'), new Clazz('To')), $dependency);
        });
    }

    public function testDoesNotAddDuplicated()
    {
        $clazzDependencies = (new ClazzDependencies())
            ->addDependency(new Dependency(new Clazz('From'), new Clazz('To')))
            ->addDependency(new Dependency(new Clazz('From'), new Clazz('To')));
        $this->assertCount(1, $clazzDependencies);
    }

    public function testFindsClassesDependingOnClass()
    {
        $clazzDependencies = (new ClazzDependencies())
            ->addDependency(new Dependency(new Clazz('From'), new Clazz('To')))
            ->addDependency(new Dependency(new Clazz('From'), new Clazz('ToAnother')));
        $dependingClasses = $clazzDependencies->classesDependingOn(new Clazz('From'))->toArray();
        $this->assertEquals(new Clazz('To'), $dependingClasses[0]);
        $this->assertEquals(new Clazz('ToAnother'), $dependingClasses[1]);
    }

    public function testReduce()
    {
        $clazzDependencies = (new ClazzDependencies())
            ->addDependency(new Dependency(new Clazz('From'), new Clazz('To')))
            ->addDependency(new Dependency(new Clazz('From'), new Clazz('ToAnother')));
        $this->assertEquals('ToToAnother', $clazzDependencies->reduce('', function (string $output, Dependency $dependency) {
            return $output.$dependency->to()->toString();
        }));
    }
}
