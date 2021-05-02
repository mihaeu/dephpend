<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use Mihaeu\PhpDependencies\Analyser\DefaultParser;
use Mihaeu\PhpDependencies\Analyser\DependencyInspectionVisitor;
use Mihaeu\PhpDependencies\Analyser\Metrics;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\XDebugFunctionTraceAnalyser;
use Mihaeu\PhpDependencies\Cli\Dispatcher;
use Mihaeu\PhpDependencies\Cli\DotCommand;
use Mihaeu\PhpDependencies\Cli\DsmCommand;
use Mihaeu\PhpDependencies\Cli\MetricsCommand;
use Mihaeu\PhpDependencies\Cli\TestFeaturesCommand;
use Mihaeu\PhpDependencies\Cli\TextCommand;
use Mihaeu\PhpDependencies\Cli\UmlCommand;
use Mihaeu\PhpDependencies\Dependencies\DependencyFactory;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixBuilder;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter;
use Mihaeu\PhpDependencies\Formatters\DotFormatter;
use Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter;
use Mihaeu\PhpDependencies\OS\DotWrapper;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use Mihaeu\PhpDependencies\OS\ShellWrapper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DependencyContainer
{
    /** @var array */
    private $internals;

    public function __construct(array $internals)
    {
        $this->internals = $internals;
    }

    public function dependencyFilter(): DependencyFilter
    {
        return new DependencyFilter($this->internals);
    }

    public function phpFileFinder(): PhpFileFinder
    {
        return new PhpFileFinder();
    }

    public function parser(): Parser
    {
        return new DefaultParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
    }

    public function dependencyFactory(): DependencyFactory
    {
        return new DependencyFactory();
    }

    public function staticAnalyser(): StaticAnalyser
    {
        return new StaticAnalyser(
            new NodeTraverser(),
            new DependencyInspectionVisitor(
                $this->dependencyFactory()
            ),
            $this->parser()
        );
    }

    public function xDebugFunctionTraceAnalyser(): XDebugFunctionTraceAnalyser
    {
        return new XDebugFunctionTraceAnalyser($this->dependencyFactory());
    }

    public function umlCommand(): UmlCommand
    {
        return new UmlCommand(new PlantUmlWrapper(new PlantUmlFormatter(), new ShellWrapper()));
    }

    public function dotCommand(): DotCommand
    {
        return new DotCommand(new DotWrapper(new DotFormatter(), new ShellWrapper()));
    }

    public function dsmCommand(): DsmCommand
    {
        return new DsmCommand(new DependencyStructureMatrixHtmlFormatter(new DependencyStructureMatrixBuilder()));
    }

    public function textCommand(): TextCommand
    {
        return new TextCommand();
    }

    public function metricsCommand(): MetricsCommand
    {
        return new MetricsCommand(new Metrics());
    }

    public function testFeaturesCommand(): TestFeaturesCommand
    {
        return new TestFeaturesCommand();
    }

    public function dispatcher(): EventDispatcherInterface
    {
        return new Dispatcher(
            $this->staticAnalyser(),
            $this->xDebugFunctionTraceAnalyser(),
            $this->phpFileFinder(),
            $this->dependencyFilter()
        );
    }
}
