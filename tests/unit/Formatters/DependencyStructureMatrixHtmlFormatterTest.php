<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter::class)]
class DependencyStructureMatrixHtmlFormatterTest extends TestCase
{
    /** @var DependencyStructureMatrixBuilder&MockObject */
    private $dependencyStructureMatrixBuilder;

    /** @var DependencyStructureMatrixHtmlFormatter */
    private $dependencyStructureMatrixHtmlFormatter;

    protected function setUp(): void
    {
        $this->dependencyStructureMatrixBuilder = $this->createMock(DependencyStructureMatrixBuilder::class);
        $this->dependencyStructureMatrixHtmlFormatter = new DependencyStructureMatrixHtmlFormatter($this->dependencyStructureMatrixBuilder);
    }

    public function testFormatsHtml(): void
    {
        $this->dependencyStructureMatrixBuilder->method('buildMatrix')->willReturn([
            'A' => ['A' => 0, 'B' => 1, 'C' => 1],
            'B' => ['A' => 0, 'B' => 0, 'C' => 1],
            'C' => ['A' => 0, 'B' => 0, 'C' => 0]
        ]);
        Assert::assertStringContainsString(
            '<table><thead>'
            .'<tr><th>X</th><th>1</th><th>2</th><th>3</th></tr>'
            .'</thead><tbody>'
            .'<tr><th>1: A</th><td>X</td><td>1</td><td>1</td></tr>'
            .'<tr><th>2: B</th><td>0</td><td>X</td><td>1</td></tr>'
            .'<tr><th>3: C</th><td>0</td><td>0</td><td>X</td></tr>'
            .'</tbody></table>',
            $this->dependencyStructureMatrixHtmlFormatter->format(new DependencyMap(), function ($x) {
                return $x;
            })
        );
    }
}
