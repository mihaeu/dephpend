<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyStructureMatrixHtmlFormatter
 * @covers Mihaeu\PhpDependencies\DependencyStructureMatrixFormatter
 */
class DependencyStructureMatrixFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatsHtml()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('A'), new Clazz('B')))
            ->add(new DependencyPair(new Clazz('A'), new Clazz('B')))
            ->add(new DependencyPair(new Clazz('A'), new Clazz('C')))
            ->add(new DependencyPair(new Clazz('B'), new Clazz('C')));
        $this->assertEquals('<table><thead>'
            .'<tr><th>X</th><th>A</th><th>B</th><th>C</th></tr>'
            .'</thead><tbody>'
            .'<tr><td>A</td><td>X</td><td>2</td><td>1</td></tr>'
            .'<tr><td>B</td><td>0</td><td>X</td><td>1</td></tr>'
            .'<tr><td>C</td><td>0</td><td>0</td><td>X</td></tr>'
            .'</tbody></table>',
            (new DependencyStructureMatrixHtmlFormatter())->format($dependencies)
        );
    }
}
