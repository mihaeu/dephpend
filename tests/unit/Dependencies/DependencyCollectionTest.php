<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

/**
 * @covers Mihaeu\PhpDependencies\Dependencies\DependencyCollection
 * @covers Mihaeu\PhpDependencies\Util\AbstractCollection
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
        $this->assertEquals('Test'.PHP_EOL.'Test2', $clazzCollection->__toString());
    }
}
