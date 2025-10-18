<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;
use Mihaeu\PhpDependencies\Util\Functional;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixBuilder::class)]
class DependencyStructureMatrixBuilderTest extends TestCase
{
    /** @var DependencyStructureMatrixBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new DependencyStructureMatrixBuilder();
    }

    public function testBuildMatrixFromClassesWithoutNamespaces(): void
    {
        $dependencies = DependencyHelper::map('
            A --> D, B
            B --> D
            C --> A
            D --> B
        ');
        $this->assertEquals([
            'A' => ['A' => 0, 'B' => 0, 'C' => 1, 'D' => 0],
            'B' => ['A' => 1, 'B' => 0, 'C' => 0, 'D' => 1],
            'C' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            'D' => ['A' => 1, 'B' => 1, 'C' => 0, 'D' => 0],
        ], $this->builder->buildMatrix($dependencies, Functional::id()));
    }

    public function testBuildMatrixFromClassesWithNamespaces(): void
    {
        $dependencies = DependencyHelper::map('
            AA\\A --> DD\\D, BB\\B
            BB\\B --> DD\\D
            CC\\C --> AA\\A
            DD\\D --> BB\\B
        ');
        $this->assertEquals([
            'AA\\A' => ['AA\\A' => 0, 'BB\\B' => 0, 'CC\\C' => 1, 'DD\\D' => 0],
            'BB\\B' => ['AA\\A' => 1, 'BB\\B' => 0, 'CC\\C' => 0, 'DD\\D' => 1],
            'CC\\C' => ['AA\\A' => 0, 'BB\\B' => 0, 'CC\\C' => 0, 'DD\\D' => 0],
            'DD\\D' => ['AA\\A' => 1, 'BB\\B' => 1, 'CC\\C' => 0, 'DD\\D' => 0],
        ], $this->builder->buildMatrix($dependencies, Functional::id()));
    }
}
