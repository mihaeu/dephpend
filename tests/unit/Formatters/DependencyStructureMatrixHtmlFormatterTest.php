<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter
 */
class DependencyStructureMatrixHtmlFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyStructureMatrixBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $dependencyStructureMatrixBuilder;

    /** @var DependencyStructureMatrixHtmlFormatter */
    private $dependencyStructureMatrixHtmlFormatter;

    public function setUp()
    {
        $this->dependencyStructureMatrixBuilder = $this->createMock(DependencyStructureMatrixBuilder::class);
        $this->dependencyStructureMatrixHtmlFormatter = new DependencyStructureMatrixHtmlFormatter($this->dependencyStructureMatrixBuilder);
    }

    public function testFormatsHtml()
    {
        $this->dependencyStructureMatrixBuilder->method('buildMatrix')->willReturn([
            'A' => ['A' => 0, 'B' => 1, 'C' => 1],
            'B' => ['A' => 0, 'B' => 0, 'C' => 1],
            'C' => ['A' => 0, 'B' => 0, 'C' => 0]
        ]);
        $this->assertEquals('<table><thead>'
            .'<tr><th>X</th><th>1</th><th>2</th><th>3</th></tr>'
            .'</thead><tbody>'
            .'<tr><th>1: A</th><td>X</td><td>1</td><td>1</td></tr>'
            .'<tr><th>2: B</th><td>0</td><td>X</td><td>1</td></tr>'
            .'<tr><th>3: C</th><td>0</td><td>0</td><td>X</td></tr>'
            .'</tbody></table>',
            $this->dependencyStructureMatrixHtmlFormatter->format(new DependencyMap())
        );
    }
}
