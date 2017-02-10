<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\Metrics;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\MetricsCommand
 */
class MetricsCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var MetricsCommand */
    private $metricsCommand;

    /** @var Metrics|\PHPUnit_Framework_MockObject_MockObject */
    private $metrics;

    /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $output;

    public function setUp()
    {
        $this->metrics = $this->createMock(Metrics::class);
        $this->metricsCommand = new MetricsCommand(
            new DependencyMap(),
            $this->metrics
        );
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testPrintsMetrics()
    {
        $this->input->method('getArgument')->willReturn([]);
        $this->input->method('getOption')->willReturn(true, 0);
        $this->input->method('getOptions')->willReturn(['internals' => false, 'filter-namespace' => null, 'depth' => 0]);

        $this->metrics->method('classCount')->willReturn(1);
        $this->metrics->method('abstractClassCount')->willReturn(2);
        $this->metrics->method('interfaceCount')->willReturn(3);
        $this->metrics->method('traitCount')->willReturn(4);
        $this->metrics->method('abstractness')->willReturn(5);

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
+--+-------------------+-------------------+-------------+
|  | Afferent Coupling | Efferent Coupling | Instability |
+--+-------------------+-------------------+-------------+
', $output->fetch());
    }
}
