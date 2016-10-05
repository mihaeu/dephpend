<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Formatters\DependencyStructureMatrixHtmlFormatter;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DsmCommand extends BaseCommand
{
    /** @var DependencyStructureMatrixHtmlFormatter */
    private $dependencyStructureMatrixHtmlFormatter;

    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser,
        DependencyStructureMatrixHtmlFormatter $dependencyStructureMatrixFormatter)
    {
        parent::__construct('dsm', $phpFileFinder, $parser, $analyser);

        $this->defaultFormat = 'html';
        $this->allowedFormats = [$this->defaultFormat];

        $this->dependencyStructureMatrixHtmlFormatter = $dependencyStructureMatrixFormatter;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Generate a Dependency Structure Matrix of your dependencies')
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
        $options = $input->getOptions();
        $this->ensureSourcesAreReadable($input->getArgument('source'));
        $this->ensureOutputFormatIsValid($options['format']);

        $dependencies = $this->preFilterByInputOptions(
            $this->detectDependencies($input->getArgument('source')),
            $options
        );
        $output->write($this->dependencyStructureMatrixHtmlFormatter->format(
            $dependencies,
            (int) $options['depth']
        ));
    }
}
