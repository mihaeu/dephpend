<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Metrics;
use Mihaeu\PhpDependencies\Parser;
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
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dependencies = $this->detectDependencies(
            $input->getArgument('source'),
            $input->getOption('internals'),
            (int) $input->getOption('depth')
        );
        $output->writeln('Classes: '.$this->metrics->classCount($dependencies));
        $output->writeln('Abstract classes: '.$this->metrics->abstractClassCount($dependencies));
        $output->writeln('Interfaces: '.$this->metrics->interfaceCount($dependencies));
        $output->writeln('Traits: '.$this->metrics->traitCount($dependencies));
        $output->writeln('Abstractness: '.$this->metrics->abstractness($dependencies));
        $output->writeln('Afferent Coupling: '.PHP_EOL.print_r($this->metrics->afferentCoupling($dependencies), true));
        $output->writeln('Efferent Coupling: '.PHP_EOL.print_r($this->metrics->efferentCoupling($dependencies), true));
        $output->writeln('Instability: '.PHP_EOL.print_r($this->metrics->instability($dependencies), true));
    }
}
