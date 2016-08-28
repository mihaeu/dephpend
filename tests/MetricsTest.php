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
            ->add(new DependencyPair(new Clazz('B'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('R'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('C'), new Trait_('B')))
            ->add(new DependencyPair(new AbstractClazz('D'), new Interfaze('E')))
            ->add(new DependencyPair(new Interfaze('B'), new Interfaze('E')))
            ->add(new DependencyPair(new Trait_('B'), new Interfaze('E')))
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
}
