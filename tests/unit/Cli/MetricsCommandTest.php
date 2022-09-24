<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Metrics;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\MetricsCommand
 */
class MetricsCommandTest extends TestCase
{
    /** @var MetricsCommand */
    private $metricsCommand;

    /** @var Metrics|PHPUnit_Framework_MockObject_MockObject */
    private $metrics;

    /** @var InputInterface|PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|PHPUnit_Framework_MockObject_MockObject */
    private $output;

    protected function setUp(): void
    {
        $this->metrics = $this->createMock(Metrics::class);
        $this->metricsCommand = new MetricsCommand($this->metrics);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testPrintsMetrics(): void
    {
        $this->input->method('getArgument')->willReturn([]);
        $this->input->method('getOption')->willReturn(true, 0);
        $this->input->method('getOptions')->willReturn(['internals' => false, 'filter-namespace' => null, 'depth' => 0]);

        $this->metrics->method('classCount')->willReturn(1);
        $this->metrics->method('abstractClassCount')->willReturn(2);
        $this->metrics->method('interfaceCount')->willReturn(3);
        $this->metrics->method('traitCount')->willReturn(4);
        $this->metrics->method('abstractness')->willReturn(5.0);
        $this->metrics->method('afferentCoupling')->willReturn(['A' => 1]);
        $this->metrics->method('efferentCoupling')->willReturn(['A' => 1]);
        $this->metrics->method('instability')->willReturn(['A' => 1]);

        $output = new BufferedOutput();
        $this->metricsCommand->run($this->input, $output);
        $this->assertEquals(
            '+--------------------+-------+
| Classes:           | 1     |
| Abstract classes:  | 2     |
| Interfaces:        | 3     |
| Traits:            | 4     |
| Abstractness:      | 5.000 |
+--------------------+-------+
+---+-------------------+-------------------+-------------+
|   | Afferent Coupling | Efferent Coupling | Instability |
+---+-------------------+-------------------+-------------+
| A | 1                 | 1                 | 1.00        |
+---+-------------------+-------------------+-------------+
',
            $output->fetch()
        );
    }
}
