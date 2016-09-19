<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Util\DI;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixBuilder;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter;
use Mihaeu\PhpDependencies\Analyser\Metrics;
use Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use Mihaeu\PhpDependencies\OS\ShellWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    /** @var DI */
    private $dI;

    /**
     * @param string $name
     * @param string $version
     * @param DI $dI
     */
    public function __construct(string $name, string $version, DI $dI)
    {
        $this->dI = $dI;

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

        $this->addCommands($this->createCommands($input));
        return parent::doRun($input, $output);
    }

    /**
     * @param OutputInterface $output
     */
    private function printWarningIfXdebugIsEnabled(OutputInterface $output)
    {
        if (extension_loaded('xdebug')) {
            $output->writeln(
                '<fg=black;bg=yellow>You are running dePHPend with xdebug enabled.'
                .' This has a major impact on runtime performance. '
                .'See https://getcomposer.org/xdebug</>'
            );
        }
    }

    /**
     * @param InputInterface $input
     *
     * @return bool
     */
    private function isUnderscoreSupportRequired(InputInterface $input)
    {
        return $input->hasParameterOption(array('--underscore-namespaces', '-u'), true);
    }

    /**
     * @param InputInterface $input
     *
     * @return Command[]
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    private function createCommands(InputInterface $input) : array
    {
        $phpFileFinder = $this->dI->phpFileFinder();
        $parser = $this->dI->parser();
        $analyser = $this->dI->analyser($this->isUnderscoreSupportRequired($input));

        return [
            new UmlCommand(
                $phpFileFinder,
                $parser,
                $analyser,
                new PlantUmlWrapper(new PlantUmlFormatter(), new ShellWrapper())
            ),
            new DsmCommand(
                $phpFileFinder,
                $parser,
                $analyser,
                new DependencyStructureMatrixHtmlFormatter(
                    new DependencyStructureMatrixBuilder()
                )
            ),
            new TextCommand(
                $phpFileFinder,
                $parser,
                $analyser
            ),
            new MetricsCommand(
                $phpFileFinder,
                $parser,
                $analyser,
                new Metrics()
            ),
            new TestFeaturesCommand(),
        ];
    }
}
