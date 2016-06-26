<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Mihaeu\PhpDependencies\PlantUmlWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UmlCommand extends BaseCommand
{
    /** @var PlantUmlWrapper */
    private $plantUmlWrapper;

    /**
     * @param PhpFileFinder   $phpFileFinder
     * @param Parser          $parser
     * @param Analyser        $analyser
     * @param PlantUmlWrapper $plantUmlWrapper
     */
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser,
        PlantUmlWrapper $plantUmlWrapper
    ) {
        parent::__construct('uml', $phpFileFinder, $parser, $analyser);

        $this->plantUmlWrapper = $plantUmlWrapper;

        $this->defaultFormat = 'png';
        $this->allowedFormats = [$this->defaultFormat];
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Generate a UML Class diagram of your dependencies')
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Destination for the generated class diagram (in .png format).'
            )
            ->addOption(
                'keep-uml',
                null,
                InputOption::VALUE_NONE,
                'Keep the intermediate PlantUML file instead of deleting it.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureSourcesAreReadable($input->getArgument('source'));
        $this->ensureOutputExists($input->getOption('output'));
        $this->ensureDestinationIsWritable($input->getOption('output'));
        $this->ensureOutputFormatIsValid($input->getOption('output'));

        $dependencies = $this->detectDependencies(
            $input->getArgument('source'),
            $input->getOption('internals'),
            $input->getOption('only-namespaces')
        );

        $destination = new \SplFileInfo($input->getOption('output'));
        $this->plantUmlWrapper->generate($dependencies, $destination, $input->getOption('keep-uml'));
    }

    /**
     * @param $outputOption
     */
    private function ensureOutputExists($outputOption)
    {
        if ($outputOption === null) {
            throw new \InvalidArgumentException('Output not defined (use "help" for more information).');
        }
    }
}
