<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Mihaeu\PhpDependencies\PlantUmlWrapper;
use Symfony\Component\Console\Input\InputArgument;
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
        $this
            ->setDescription('Generate a UML Class diagram of your dependencies')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'Location of your PHP source files.'
            )
            ->addArgument(
                'destination',
                InputArgument::REQUIRED,
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
        $this->ensureSourceIsReadable($input->getArgument('source'));
        $this->ensureDestinationIsWritable($input->getArgument('destination'));
        $this->ensureOutputFormatIsValid($input->getArgument('destination'));

        $source = new \SplFileInfo($input->getArgument('source'));
        $dependencies = $this->detectDependencies($source);

        $destination = new \SplFileInfo($input->getArgument('destination'));
        $this->plantUmlWrapper->generate($dependencies, $destination, $input->getOption('keep-uml'));
    }
}
