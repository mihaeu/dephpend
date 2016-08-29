<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyStructureMatrixBuilderTest
 */
class DependencyStructureMatrixBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var DependencyStructureMatrixBuilder */
    private $builder;
    
    public function setUp()
    {
        $this->builder = new DependencyStructureMatrixBuilder();
    }
    
    public function testBuildMatrix()
    {
        $dependencies = (new DependencyPairCollection());
        $this->assertEquals([], $this->builder->buildMatrix($dependencies));
    }
}
