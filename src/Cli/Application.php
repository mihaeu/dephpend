<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\ClazzFactory;
use Mihaeu\PhpDependencies\DependencyInspectionVisitor;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Mihaeu\PhpDependencies\PlantUmlFormatter;
use Mihaeu\PhpDependencies\PlantUmlWrapper;
use Mihaeu\PhpDependencies\ShellWrapper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->printWarningIfXdebugIsEnabled($output);
        $this->setMemoryLimit($input);

        $phpFileFinder = new PhpFileFinder();
        $parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
        $dependencyInspectionVisitor = new DependencyInspectionVisitor(new ClazzFactory());
        $analyser = new Analyser(new NodeTraverser(), $dependencyInspectionVisitor);

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
}
