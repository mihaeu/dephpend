<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Clazz;
use Mihaeu\PhpDependencies\DependencyPair;
use Mihaeu\PhpDependencies\DependencyPairCollection;
use Mihaeu\PhpDependencies\Interfaze;
use Mihaeu\PhpDependencies\Metrics;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\Trait_;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MetricsCommand extends BaseCommand
{
    /** @var Metrics */
    private $metrics;

    /**
     * @param PhpFileFinder $phpFileFinder
     * @param Parser $parser
     * @param Analyser $analyser
     */
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser,
        Metrics $metrics
    ) {
        $this->metrics = $metrics;

        parent::__construct('metrics', $phpFileFinder, $parser, $analyser);
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Generate dependency metrics')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(print_r($this->metrics->computeMetrics($this->detectDependencies(
            $input->getArgument('source'),
            $input->getOption('internals'),
            (int) $input->getOption('depth')
        )), true));
    }
}
