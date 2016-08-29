<?php declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\DependencyStructureMatrixBuilder
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
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('A'), new Clazz('D')))
            ->add(new DependencyPair(new Clazz('A'), new Clazz('B')))
            ->add(new DependencyPair(new Clazz('B'), new Clazz('D')))
            ->add(new DependencyPair(new Clazz('C'), new Clazz('A')))
            ->add(new DependencyPair(new Clazz('D'), new Clazz('B')))
        ;
        $this->assertEquals([
            'A' => ['A' => 0, 'B' => 1, 'C' => 0, 'D' => 1],
            'B' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 1],
            'C' => ['A' => 1, 'B' => 0, 'C' => 0, 'D' => 0],
            'D' => ['A' => 0, 'B' => 1, 'C' => 0, 'D' => 0],
        ], $this->builder->buildMatrix($dependencies));
    }
}
