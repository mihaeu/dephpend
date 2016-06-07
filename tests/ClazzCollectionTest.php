<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\ClazzCollection
 * @covers Mihaeu\PhpDependencies\AbstractCollection
 *
 * @uses Mihaeu\PhpDependencies\Clazz
 */
class ClazzCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $clazzCollection = (new ClazzCollection())
            ->add(new Clazz('Test'));
        $clazzCollection->each(function (Clazz $clazz) {
            $this->assertEquals(new Clazz('Test'), $clazz);
        });
    }

    public function testToArray()
    {
        $clazzCollection = (new ClazzCollection())
            ->add(new Clazz('Test'));
        $this->assertEquals([new Clazz('Test')], $clazzCollection->toArray());
    }
}
