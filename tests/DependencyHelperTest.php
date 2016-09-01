<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyPairCollection;
use Mihaeu\PhpDependencies\Dependencies\Namespaze;

/**
 * @covers Mihaeu\PhpDependencies\DependencyHelper
 */
class DependencyHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $expected = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('DepA', new Namespaze(['A'])), new Clazz('DepB', new Namespaze(['B']))))
            ->add(new DependencyPair(new Clazz('DepC', new Namespaze(['C'])), new Clazz('DepD', new Namespaze(['D']))))
        ;
        $this->assertEquals($expected, DependencyHelper::convert('
            A\\DepA --> B\\DepB
            C\\DepC --> D\\DepD
        '));
    }
}
