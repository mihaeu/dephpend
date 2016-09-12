<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyPairSet;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;

/**
 * @covers Mihaeu\PhpDependencies\DependencyHelper
 */
class DependencyHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $expected = (new DependencyPairSet())
            ->add((new DependencyPair(new Clazz('DepA', new Namespaze(['A']))))
                ->addDependency(new Clazz('DepB', new Namespaze(['B']))))
            ->add((new DependencyPair(new Clazz('DepC', new Namespaze(['C']))))
                ->addDependency(new Clazz('DepD', new Namespaze(['D']))))
        ;
        $this->assertEquals($expected, DependencyHelper::convert('
            A\\DepA --> B\\DepB
            C\\DepC --> D\\DepD
        '));
    }

    public function testDependencyPair()
    {
        $expected = (new DependencyPair(new Clazz('Test', new Namespaze(['A']))))
            ->addDependency(new Clazz('Test', new Namespaze(['B'])))
            ->addDependency(new Clazz('Test', new Namespaze(['C'])));
        $this->assertEquals($expected, DependencyHelper::dependencyPair('A\\Test --> B\\Test, C\\Test'));
    }
}
