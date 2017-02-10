<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;

/**
 * @covers Mihaeu\PhpDependencies\DependencyHelper
 */
class DependencyHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testConvert()
    {
        $expected = (new DependencyMap())->add(
            new Clazz('DepA', new Namespaze(['A'])),
            new Clazz('DepB', new Namespaze(['B']))
        )->add(
            new Clazz('DepC', new Namespaze(['C'])),
            new Clazz('DepD', new Namespaze(['D']))
        );
        ;
        $this->assertEquals($expected, DependencyHelper::map('
            A\\DepA --> B\\DepB
            C\\DepC --> D\\DepD
        '));
    }

    public function testConvertMultipleDependencies()
    {
        $expected = (new DependencyMap())->add(
            new Clazz('DepA', new Namespaze(['A'])),
            new Clazz('DepB', new Namespaze(['B']))
        )->add(
            new Clazz('DepA', new Namespaze(['A'])),
            new Clazz('DepD', new Namespaze(['D']))
        );
        ;
        $this->assertEquals($expected, DependencyHelper::map('
            A\\DepA --> B\\DepB, D\\DepD
        '));
    }
}
