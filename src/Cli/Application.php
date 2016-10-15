<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Metrics;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixBuilder;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter;
use Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use Mihaeu\PhpDependencies\OS\ShellWrapper;
use Mihaeu\PhpDependencies\Util\DI;
use Mihaeu\PhpDependencies\Util\Functional;
use PhpParser\Error;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    const XDEBUG_WARNING = 'You are running dePHPend with xdebug enabled. This has a major impact on runtime performance. See https://getcomposer.org/xdebug';

    /**
     * @param string $name
     * @param string $version
     * @param DI $dI
     *
     * @throws \LogicException
     */
    public function __construct(string $name, string $version, DI $dI)
    {
        $this->setHelperSet($this->getDefaultHelperSet());
        $this->addCommands($this->createCommands($dI, $this->createFakeInput()));

        parent::__construct($name, $version);
    }

    /**
     * Commands are added here instead of before executing run(), because
     * we need access to command line options in order to inject the
     * right dependencies.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->printWarningIfXdebugIsEnabled($output);

        try {
            parent::doRun($input, $output);
        } catch (Error $e) {
            $output->writeln('<error>Sorry, we could not analyse your dependencies, '
                .'because the sources contain syntax errors:'.PHP_EOL.PHP_EOL
                .$e->getMessage().'<error>'
            );
            return 1;
        }
        return 0;
    }

    /**
     * @param OutputInterface $output
     */
    private function printWarningIfXdebugIsEnabled(OutputInterface $output)
    {
        if (extension_loaded('xdebug')) {
            $output->writeln('<fg=black;bg=yellow>'.self::XDEBUG_WARNING.'</>');
        }
    }

    /**
     * @param DI $dI
     * @param InputInterface $input
     *
     * @return Command[]
     *
     * @throws \LogicException
     */
    private function createCommands(DI $dI, InputInterface $input) : array
    {
        $dependencies = $this->analyzeDependencies($input, $dI);
        $postProcessors = $this->getPostProcessors($input, $dI->dependencyFilter());

        return [
            new UmlCommand(
                $dependencies,
                $postProcessors,
                new PlantUmlWrapper(new PlantUmlFormatter(), new ShellWrapper())
            ),
            new DsmCommand(
                $dependencies,
                $postProcessors,
                new DependencyStructureMatrixHtmlFormatter(
                    new DependencyStructureMatrixBuilder()
                )
            ),
            new TextCommand(
                $dependencies,
                $postProcessors
            ),
            new MetricsCommand(
                $dependencies,
                new Metrics()
            ),
            new TestFeaturesCommand(),
        ];
    }

    /**
     * This is an ugly hack which is needed because of the late argument binding
     * of the Symfony console component. We don't want to pass our DI around our
     * application but we need input in order to make decisions for which
     * dependencies to inject.
     *
     * In order to parse and validate input Symfony requires the definitions of
     * a command. I use the TextCommand here because it contains all important
     * definitions for all the other commands.
     */
    private function createFakeInput() : InputInterface
    {
        if ($this->noDephpendCommandProvided()) {
            return new ArrayInput([]);
        }
        $textCommand = new TextCommand(new DependencyMap(), Functional::id());
        $textCommand->mergeApplicationDefinition();
        $argvInput = new ArgvInput(array_slice($_SERVER['argv'], 1),
            $textCommand->getDefinition());
        $textCommand->setDefinition(new InputDefinition());
        return $argvInput;
    }

    /**
     * @param InputInterface $input
     * @param DI $dI
     *
     * @return DependencyMap
     */
    private function analyzeDependencies(InputInterface $input, DI $dI) : DependencyMap
    {
        if ($this->noDephpendCommandProvided()) {
            return new DependencyMap();
        }

        $filter = $dI->dependencyFilter();

        // run static analysis
        $dependencies = $dI->staticAnalyser()->analyse(
            $dI->parser()->parse(
                $dI->phpFileFinder()->getAllPhpFilesFromSources($input->getArgument('source'))
            )
        );

        // optional: analyse results of dynamic analysis and merge
        if ($input->getOption('dynamic')) {
            $traceFile = new \SplFileInfo($input->getOption('dynamic'));
            $dependencies = $dependencies->addMap(
                $dI->xDebugFunctionTraceAnalyser()->analyse($traceFile)
            );
        }

        // apply pre-filters
        return $filter->filterByOptions($dependencies, $input->getOptions());
    }

    private function getPostProcessors(InputInterface $input, DependencyFilter $filter) : \Closure
    {
        return $this->noDephpendCommandProvided()
            ? Functional::id()
            : $filter->postFiltersByOptions($input->getOptions());
    }

    /**
     *
     * @return bool
     */
    private function noDephpendCommandProvided() : bool
    {
        return count($_SERVER['argv']) < 2
            || $_SERVER['argv'][1] === 'help'
            || $_SERVER['argv'][1] === 'list';
    }
}
