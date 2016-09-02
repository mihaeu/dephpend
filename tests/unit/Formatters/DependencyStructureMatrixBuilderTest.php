<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\DependencyHelper;

/**
 * @covers Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixBuilder
 */
class DependencyStructureMatrixBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyStructureMatrixBuilder */
    private $builder;

    public function setUp()
    {
        $this->builder = new DependencyStructureMatrixBuilder();
    }

    public function testBuildMatrixFromClassesWithoutNamespaces()
    {
        $dependencies = DependencyHelper::convert('
            A --> D
            A --> B
            B --> D
            C --> A
            D --> B
        ');
        $this->assertEquals([
            'A' => ['A' => 0, 'B' => 1, 'C' => 0, 'D' => 1],
            'B' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 1],
            'C' => ['A' => 1, 'B' => 0, 'C' => 0, 'D' => 0],
            'D' => ['A' => 0, 'B' => 1, 'C' => 0, 'D' => 0],
        ], $this->builder->buildMatrix($dependencies));
    }

    public function testBuildMatrixFromClassesWithNamespaces()
    {
        $dependencies = DependencyHelper::convert('
            AA\\A --> DD\\D
            AA\\A --> BB\\B
            BB\\B --> DD\\D
            CC\\C --> AA\\A
            DD\\D --> BB\\B
        ');
        $this->assertEquals([
            'AA\\A' => ['AA\\A' => 0, 'BB\\B' => 1, 'CC\\C' => 0, 'DD\\D' => 1],
            'BB\\B' => ['AA\\A' => 0, 'BB\\B' => 0, 'CC\\C' => 0, 'DD\\D' => 1],
            'CC\\C' => ['AA\\A' => 1, 'BB\\B' => 0, 'CC\\C' => 0, 'DD\\D' => 0],
            'DD\\D' => ['AA\\A' => 0, 'BB\\B' => 1, 'CC\\C' => 0, 'DD\\D' => 0],
        ], $this->builder->buildMatrix($dependencies));
    }
}
