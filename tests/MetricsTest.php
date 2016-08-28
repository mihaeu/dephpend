<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

/**
 * @covers Mihaeu\PhpDependencies\Metrics
 */
class MetricsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Metrics */
    private $metrics;

    /** @var DependencyPairCollection */
    private $dependencies;

    public function setUp()
    {
        $this->dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('A'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('G'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('R'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('C'), new Trait_('F')))
            ->add(new DependencyPair(new AbstractClazz('D'), new Interfaze('E')))
            ->add(new DependencyPair(new Interfaze('B'), new Interfaze('E')))
            ->add(new DependencyPair(new Trait_('H'), new Interfaze('E')))
        ;
        $this->metrics = new Metrics($this->dependencies);
    }

    public function testCountClasses()
    {
        $this->assertEquals(4, $this->metrics->classCount($this->dependencies));
    }

    public function testCountInterfaces()
    {
        $this->assertEquals(1, $this->metrics->interfaceCount($this->dependencies));
    }

    public function testCountAbstractClasses()
    {
        $this->assertEquals(1, $this->metrics->abstractClassCount($this->dependencies));
    }

    public function testCountTraits()
    {
        $this->assertEquals(1, $this->metrics->traitCount($this->dependencies));
    }

    public function testComputeAbstractness()
    {
        $this->assertEquals(0.428, $this->metrics->abstractness($this->dependencies), '', 0.001);
    }

    public function testComputeAfferentCoupling()
    {
        $this->assertEquals([
            'A' => 0,
            'G' => 0,
            'R' => 0,
            'C' => 0,
            'D' => 0,
            'B' => 3, // three classes depend on B
            'H' => 0,
        ], $this->metrics->afferentCoupling($this->dependencies));
    }

    public function testComputeEfferentCoupling()
    {
        // all my classes depend only on one dependency
        $this->assertEquals([
            'A' => 1,
            'G' => 1,
            'R' => 1,
            'C' => 1,
            'D' => 1,
            'B' => 1,
            'H' => 1,
        ], $this->metrics->efferentCoupling($this->dependencies));
    }
}
