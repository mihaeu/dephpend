<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @covers Mihaeu\PhpDependencies\Cli\Dispatcher
 */
class DispatcherTest extends TestCase
{
    /** @var Dispatcher */
    private $dispatcher;

    /** @var StaticAnalyser | MockObject */
    private $staticAnalyser;

    /** @var XDebugFunctionTraceAnalyser | MockObject */
    private $xDebugFunctionTraceAnalyser;

    /** @var PhpFileFinder | MockObject */
    private $phpFileFinder;

    /** @var DependencyFilter | MockObject */
    private $dependencyFilter;

    protected function setUp(): void
    {
        $this->staticAnalyser = $this->createMock(StaticAnalyser::class);
        $this->xDebugFunctionTraceAnalyser = $this->createMock(XDebugFunctionTraceAnalyser::class);
        $this->phpFileFinder = $this->createMock(PhpFileFinder::class);
        $this->dependencyFilter = $this->createMock(DependencyFilter::class);

        $this->dispatcher = new Dispatcher(
            $this->staticAnalyser,
            $this->xDebugFunctionTraceAnalyser,
            $this->phpFileFinder,
            $this->dependencyFilter
        );
    }

    public function testTriggersOnlyOnNamedConsoleEvents(): void
    {
        $consoleEvent = $this->createMock(ConsoleEvent::class);
        $consoleEvent->expects(never())->method('getInput');
        $this->dispatcher->dispatch($consoleEvent, 'other event');
    }

    public function testTriggersOnlyOnConsoleEvents(): void
    {
        $consoleEvent = $this->createMock(GenericEvent::class);
        assertEquals(
            $consoleEvent,
            $this->dispatcher->dispatch(clone $consoleEvent, ConsoleEvents::COMMAND)
        );
    }

    public function testInjectsDependenciesIntoConsoleEvents(): void
    {
        $command = $this->createMock(BaseCommand::class);

        $consoleEvent = $this->createMock(ConsoleEvent::class);
        $consoleEvent->method('getCommand')->willReturn($command);

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('source')->willReturn([]);
        $input->method('getOptions')->willReturn([]);
        $traceFile = sys_get_temp_dir();
        $input->method('getOption')->with('dynamic')->willReturn($traceFile);
        $consoleEvent->method('getInput')->willReturn($input);

        $this->xDebugFunctionTraceAnalyser->expects(once())->method('analyse')->with($traceFile);
        $command->expects(once())->method('setDependencies');
        $command->expects(once())->method('setPostProcessors');
        $this->dispatcher->dispatch(clone $consoleEvent, ConsoleEvents::COMMAND);
    }
}
