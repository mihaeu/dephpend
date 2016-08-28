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

    public function setUp()
    {
        $this->metrics = new Metrics();
    }

    public function testComputesMetrics()
    {
        $dependencies = (new DependencyPairCollection())
            ->add(new DependencyPair(new Clazz('A'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('B'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('R'), new Interfaze('B')))
            ->add(new DependencyPair(new Clazz('C'), new Trait_('B')))
            ->add(new DependencyPair(new AbstractClazz('D'), new Interfaze('E')))
            ->add(new DependencyPair(new Interfaze('B'), new Interfaze('E')))
            ->add(new DependencyPair(new Trait_('B'), new Interfaze('E')))
        ;
        $actual = $this->metrics->computeMetrics($dependencies);
        $this->assertEquals(4, $actual['classes']);
        $this->assertEquals(1, $actual['abstractClasses']);
        $this->assertEquals(1, $actual['interfaces']);
        $this->assertEquals(1, $actual['traits']);
        $this->assertEquals(0.428, $actual['abstractness'], '', 0.001);
    }
}
