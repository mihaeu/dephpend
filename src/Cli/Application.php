<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\DI;
use Mihaeu\PhpDependencies\Metrics;
use Mihaeu\PhpDependencies\PlantUmlFormatter;
use Mihaeu\PhpDependencies\PlantUmlWrapper;
use Mihaeu\PhpDependencies\ShellWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    /** @var DI */
    private $dI;

    /**
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name, string $version, DI $dI)
    {
        $this->dI = $dI;

        parent::__construct($name, $version);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->printWarningIfXdebugIsEnabled($output);
        $this->setMemoryLimit($input);

        $phpFileFinder = $this->dI->phpFileFinder();
        $parser = $this->dI->parser();
        $analyser = $this->dI->analyser($this->isUnderscoreSupportRequired($input));

        $this->add(new UmlCommand(
            $phpFileFinder,
            $parser,
            $analyser,
            new PlantUmlWrapper(new PlantUmlFormatter(), new ShellWrapper())
        ));

        $this->add(new DsmCommand(
            $phpFileFinder,
            $parser,
            $analyser,
            new \Mihaeu\PhpDependencies\DependencyStructureMatrixHtmlFormatter()
        ));

        $this->add(new TextCommand(
            $phpFileFinder,
            $parser,
            $analyser
        ));

        $this->add(new MetricsCommand(
            $phpFileFinder,
            $parser,
            $analyser,
            new Metrics()
        ));

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
     */
    private function setMemoryLimit(InputInterface $input)
    {
        if ($input->hasOption('memory') && $input->getOption('memory')) {
            ini_set('memory_limit', $input->getOption('memory'));
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
}
