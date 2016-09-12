<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencySet
 * @covers Mihaeu\PhpDependencies\Util\AbstractCollection
 */
class DependencySetTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $clazzCollection->each(function (Dependency $clazz) {
            $this->assertEquals(new Clazz('Test'), $clazz);
        });
    }

    public function testIsImmutable()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $newCollectionAfterRefusingDuplicate = $clazzCollection->add(new Clazz('Test'));
        $this->assertNotSame($clazzCollection, $newCollectionAfterRefusingDuplicate);
    }

    public function testDoesNotAcceptDuplicates()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $this->assertEquals($clazzCollection, $clazzCollection->add(new Clazz('Test')));
    }

    public function testToArray()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'));
        $this->assertEquals([new Clazz('Test')], $clazzCollection->toArray());
    }

    public function testToString()
    {
        $clazzCollection = (new DependencySet())
            ->add(new Clazz('Test'))
            ->add(new Clazz('Test2'));
        $this->assertEquals('Test'.PHP_EOL.'Test2', $clazzCollection->__toString());
    }
}
