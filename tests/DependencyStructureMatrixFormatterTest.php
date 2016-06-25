<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyStructureMatrixFormatter
 */
class DependencyStructureMatrixFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatsHtml()
    {
        $dependencies = (new DependencyCollection())
            ->add(new Dependency(new Clazz('A'), new Clazz('B')))
            ->add(new Dependency(new Clazz('A'), new Clazz('C')))
            ->add(new Dependency(new Clazz('B'), new Clazz('C')));
        $this->assertEquals('', (new DependencyStructureMatrixFormatter())->format($dependencies));
    }
}
