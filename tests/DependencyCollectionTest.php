<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyCollection
 * @covers Mihaeu\PhpDependencies\AbstractCollection
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 */
class DependencyCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $clazzCollection = (new DependencyCollection())
            ->add(new Clazz('Test'));
        $clazzCollection->each(function (Dependency $clazz) {
            $this->assertEquals(new Clazz('Test'), $clazz);
        });
    }

    public function testToArray()
    {
        $clazzCollection = (new DependencyCollection())
            ->add(new Clazz('Test'));
        $this->assertEquals([new Clazz('Test')], $clazzCollection->toArray());
    }

    public function testToString()
    {
        $clazzCollection = (new DependencyCollection())
            ->add(new Clazz('Test'))
            ->add(new Clazz('Test2'));
        $this->assertEquals('Test'.PHP_EOL.'Test2', $clazzCollection->toString());
    }
}
