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
            ->add(new DependencyPair(new Clazz('C'), new Trait_('B')));
        $this->assertEquals([
            'classes'    => 3,
            'interfaces' => 1,
            'traits'     => 1,
        ], $this->metrics->computeMetrics($dependencies));
    }
}
