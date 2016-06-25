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
        $this->assertEquals('<table>'
            .'<tr><th></th><th>A</th><th>B</th><th>C</th></tr>'
            .'<tr><td>A</td><td>0</td><td>1</td><td>1</td></tr>'
            .'<tr><td>B</td><td>0</td><td>0</td><td>1</td></tr>'
            .'<tr><td>C</td><td>0</td><td>0</td><td>0</td></tr>'
            .'</table>',
            (new DependencyStructureMatrixFormatter())->format($dependencies)
        );
    }
}
