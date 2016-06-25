<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\DependencyStructureMatrixFormatter;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DsmCommand extends BaseCommand
{
    /** @var DependencyStructureMatrixFormatter */
    private $dependencyStructureMatrixFormatter;

    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser,
        DependencyStructureMatrixFormatter $dependencyStructureMatrixFormatter)
    {
        parent::__construct('dsm', $phpFileFinder, $parser, $analyser);

        $this->defaultFormat = 'html';
        $this->allowedFormats = [$this->defaultFormat];

        $this->dependencyStructureMatrixFormatter = $dependencyStructureMatrixFormatter;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate a Dependency Structure Matrix of your dependencies')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'Location of your PHP source files.'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Output format.',
                'html'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureSourceIsReadable($input->getArgument('source'));
        $this->ensureOutputFormatIsValid($input->getOption('format'));

        $source = new \SplFileInfo($input->getArgument('source'));
        $output->write($this->dependencyStructureMatrixFormatter->format($this->detectDependencies($source)));
    }
}
