<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Metrics;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers Mihaeu\PhpDependencies\Cli\MetricsCommand
 */
class MetricsCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var MetricsCommand */
    private $metricsCommand;

    /** @var Metrics|\PHPUnit_Framework_MockObject_MockObject */
    private $metrics;

    /** @var InputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $input;

    /** @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $output;

    /** @var PhpFileFinder|\PHPUnit_Framework_MockObject_MockObject */
    private $phpFileFinder;

    /** @var Parser|\PHPUnit_Framework_MockObject_MockObject */
    private $parser;

    /** @var Analyser|\PHPUnit_Framework_MockObject_MockObject */
    private $analyser;

    public function setUp()
    {
        $this->phpFileFinder = $this->createMock(PhpFileFinder::class);
        $this->parser = $this->createMock(Parser::class);
        $this->analyser = $this->createMock(Analyser::class);
        $this->metrics = $this->createMock(Metrics::class);
        $this->metricsCommand = new MetricsCommand(
            $this->phpFileFinder,
            $this->parser,
            $this->analyser,
            $this->createMock(DependencyFilter::class),
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
        $this->metrics->method('abstractClassCount')->willReturn(1);
        $this->metrics->method('interfaceCount')->willReturn(1);
        $this->metrics->method('traitCount')->willReturn(1);
        $this->metrics->method('abstractness')->willReturn(1);

        $this->output->expects($this->exactly(8))->method('writeln')->withAnyParameters();

        $this->metricsCommand->run($this->input, $this->output);
    }
}
