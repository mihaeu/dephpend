<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Metrics;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Helper\Table;
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
     * @param Metrics $metrics
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser,
        DependencyFilter $dependencyFilter,
        Metrics $metrics
    ) {
        $this->metrics = $metrics;
        parent::__construct('metrics', $phpFileFinder, $parser, $analyser, $dependencyFilter);
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
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        $dependencies = $this->dependencyFilter->filterByOptions(
            $this->detectDependencies($input->getArgument('source')),
            $options
        );

        $table = new Table($output);
        $table->setRows([
            ['Classes: ', $this->metrics->classCount($dependencies)],
            ['Abstract classes: ', $this->metrics->abstractClassCount($dependencies)],
            ['Interfaces: ', $this->metrics->interfaceCount($dependencies)],
            ['Traits: ', $this->metrics->traitCount($dependencies)],
            ['Abstractness: ', sprintf('%.3f', $this->metrics->abstractness($dependencies))],
        ]);
        $table->render();

        $table = new Table($output);
        $table->setHeaders(['', 'Afferent Coupling', 'Efferent Coupling', 'Instability']);
        $table->setRows($this->combineMetrics(
            $this->metrics->afferentCoupling($dependencies),
            $this->metrics->efferentCoupling($dependencies),
            $this->metrics->instability($dependencies)
        ));
        $table->render();
    }

    private function combineMetrics(array $ca, array $ce, array $instability) : array
    {
        $result = [];
        foreach ($ca as $className => $caValue) {
            $result[] = [
                $className,
                $caValue,
                $ce[$className],
                sprintf('%.2f', $instability[$className])
            ];
        }
        return $result;
    }
}
