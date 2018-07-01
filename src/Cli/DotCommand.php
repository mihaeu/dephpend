<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\OS\DotWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DotCommand extends BaseCommand
{
    /** @var DotWrapper */
    private $dotWrapper;

    public function __construct(DotWrapper $dotWrapper)
    {
        parent::__construct('dot');

        $this->dotWrapper = $dotWrapper;

        $this->defaultFormat = 'png';
        $this->allowedFormats = [$this->defaultFormat, 'svg'];
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Generate a dot graph of your dependencies')
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Destination for the generated dot graph (in '.implode('/', [$this->defaultFormat, 'svg']).' format).'
            )
            ->addOption(
                'keep',
                null,
                InputOption::VALUE_NONE,
                'Keep the intermediate dot file instead of deleting it.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        $this->ensureDestinationIsWritable($options['output']);
        $this->ensureOutputFormatIsValid($options['output']);

        $this->dotWrapper->generate(
            $this->dependencies->reduceEachDependency($this->postProcessors),
            new \SplFileInfo($options['output']),
            $options['keep']
        );
    }
}
