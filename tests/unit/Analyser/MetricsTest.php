<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\AbstractClazz;
use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\Interfaze;
use Mihaeu\PhpDependencies\Dependencies\Trait_;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers Mihaeu\PhpDependencies\Analyser\Metrics
 */
class MetricsTest extends TestCase
{
    /** @var Metrics */
    private $metrics;

    /** @var DependencyMap */
    private $dependencies;

    protected function setUp(): void
    {
        $this->dependencies = (new DependencyMap())
            ->add(new Clazz('A'), new Interfaze('B'))
            ->add(new Clazz('G'), new Interfaze('B'))
            ->add(new Clazz('R'), new Interfaze('B'))
            ->add(new Clazz('C'), new Trait_('F'))
            ->add(new AbstractClazz('D'), new Interfaze('E'))
            ->add(new Interfaze('B'), new Interfaze('E'))
            ->add(new Trait_('H'), new Interfaze('E'))
        ;
        $this->metrics = new Metrics();
    }

    public function testAbstractnessWithNoDependency(): void
    {
        $this->assertEquals(0, (new Metrics())->abstractness(new DependencyMap()));
    }

    public function testCountClasses(): void
    {
        $this->assertEquals(4, $this->metrics->classCount($this->dependencies));
    }

    public function testCountInterfaces(): void
    {
        $this->assertEquals(1, $this->metrics->interfaceCount($this->dependencies));
    }

    public function testCountAbstractClasses(): void
    {
        $this->assertEquals(1, $this->metrics->abstractClassCount($this->dependencies));
    }

    public function testCountTraits(): void
    {
        $this->assertEquals(1, $this->metrics->traitCount($this->dependencies));
    }

    public function testComputeAbstractness(): void
    {
        Assert::assertEqualsWithDelta(0.428, $this->metrics->abstractness($this->dependencies), 0.001);
    }

    public function testComputeAfferentCoupling(): void
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

    public function testComputeEfferentCoupling(): void
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

    public function testComputeInstability(): void
    {
        $this->assertEquals([
            'A' => 1,
            'G' => 1,
            'R' => 1,
            'C' => 1,
            'D' => 1,
            'B' => 0.25,
            'H' => 1,
        ], $this->metrics->instability($this->dependencies));
    }
}
