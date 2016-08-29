<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyStructureMatrixHtmlFormatter
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
            .'<tr><th>X</th><th>A</th><th>B</th><th>C</th></tr>'
            .'</thead><tbody>'
            .'<tr><td>A</td><td>X</td><td>1</td><td>1</td></tr>'
            .'<tr><td>B</td><td>0</td><td>X</td><td>1</td></tr>'
            .'<tr><td>C</td><td>0</td><td>0</td><td>X</td></tr>'
            .'</tbody></table>',
            $this->dependencyStructureMatrixHtmlFormatter->format(new DependencyPairCollection())
        );
    }
}
