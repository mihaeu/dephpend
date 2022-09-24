<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Metrics;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MetricsCommand extends BaseCommand
{
    /** @var Metrics */
    private $metrics;

    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
        parent::__construct('metrics');
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
        $table = new Table($output);
        $table->setRows([
            ['Classes: ', $this->metrics->classCount($this->dependencies)],
            ['Abstract classes: ', $this->metrics->abstractClassCount($this->dependencies)],
            ['Interfaces: ', $this->metrics->interfaceCount($this->dependencies)],
            ['Traits: ', $this->metrics->traitCount($this->dependencies)],
            ['Abstractness: ', sprintf('%.3f', $this->metrics->abstractness($this->dependencies))],
        ]);
        $table->render();

        $table = new Table($output);
        $table->setHeaders(['', 'Afferent Coupling', 'Efferent Coupling', 'Instability']);
        $table->setRows($this->combineMetrics(
            $this->metrics->afferentCoupling($this->dependencies),
            $this->metrics->efferentCoupling($this->dependencies),
            $this->metrics->instability($this->dependencies)
        ));
        $table->render();

        return 0;
    }

    private function combineMetrics(array $afferentCoupling, array $efferentCoupling, array $instability): array
    {
        $result = [];
        foreach ($afferentCoupling as $className => $afferentCouplingValue) {
            $result[] = [
                $className,
                $afferentCouplingValue,
                $efferentCoupling[$className],
                sprintf('%.2f', $instability[$className])
            ];
        }
        return $result;
    }
}
