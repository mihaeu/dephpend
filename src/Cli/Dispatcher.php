<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Mihaeu\PhpDependencies\Util\DependencyContainer;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Dispatcher extends EventDispatcher
{
    /** @var StaticAnalyser */
    private $staticAnalyser;

    /** @var XDebugFunctionTraceAnalyser */
    private $xDebugFunctionTraceAnalyser;

    /** @var PhpFileFinder */
    private $phpFileFinder;

    /** @var DependencyFilter */
    private $dependencyFilter;

    public function __construct(
        StaticAnalyser $staticAnalyser,
        XDebugFunctionTraceAnalyser $xDebugFunctionTraceAnalyser,
        PhpFileFinder $phpFileFinder,
        DependencyFilter $dependencyFilter
    ) {
        $this->staticAnalyser = $staticAnalyser;
        $this->xDebugFunctionTraceAnalyser = $xDebugFunctionTraceAnalyser;
        $this->phpFileFinder = $phpFileFinder;
        $this->dependencyFilter = $dependencyFilter;
    }

    public function dispatch($eventName, Event $event = null)
    {
        $event = parent::dispatch($eventName, $event);

        if ($eventName !== ConsoleEvents::COMMAND
            || !($event instanceof ConsoleEvent)
        ) {
            return $event;
        }

        $command = $event->getCommand();
        if ($command instanceof BaseCommand) {
            $dependencies = $this->analyzeDependencies($event->getInput());
            $postProcessors = $this->getPostProcessors($event->getInput());
            $command->setDependencies($dependencies);
            $command->setPostProcessors($postProcessors);
        }

        return $event;
    }


    /**
     * @param InputInterface $input
     * @param DependencyContainer $dependencyContainer
     *
     * @return DependencyMap
     *
     * @throws \LogicException
     */
    private function analyzeDependencies(InputInterface $input): DependencyMap
    {
        // run static analysis
        $dependencies = $this->staticAnalyser->analyse(
            $this->phpFileFinder->getAllPhpFilesFromSources($input->getArgument('source'))
        );

        // optional: analyse results of dynamic analysis and merge
        if ($input->getOption('dynamic')) {
            $traceFile = new \SplFileInfo($input->getOption('dynamic'));
            $dependencies = $dependencies->addMap(
                $this->xDebugFunctionTraceAnalyser->analyse($traceFile)
            );
        }

        // apply pre-filters
        return $this->dependencyFilter->filterByOptions($dependencies, $input->getOptions());
    }

    private function getPostProcessors(InputInterface $input): \Closure
    {
        return $this->dependencyFilter->postFiltersByOptions($input->getOptions());
    }
}
