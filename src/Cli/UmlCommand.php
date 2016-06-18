<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Mihaeu\PhpDependencies\PlantUmlWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UmlCommand extends Command
{
    /** @var PhpFileFinder */
    private $phpFileFinder;

    /** @var Parser */
    private $parser;

    /** @var Analyser */
    private $analyser;

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
        parent::__construct('uml');

        $this->phpFileFinder = $phpFileFinder;
        $this->parser = $parser;
        $this->analyser = $analyser;
        $this->plantUmlWrapper = $plantUmlWrapper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate a UML Class diagram of your dependencies')
            ->addArgument(
                'source',
                InputArgument::REQUIRED,
                'Who do you want to greet?'
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
        $this->ensureSourceIsReadable($input);
        $this->ensureDestinationIsWritable($input);

        $files = $this->phpFileFinder->find(new \SplFileInfo($input->getArgument('source')));
        $ast = $this->parser->parse($files);
        $dependencies = $this->analyser->analyse($ast);

        $destination = new \SplFileInfo($input->getArgument('destination'));
        $this->plantUmlWrapper->generate($dependencies, $destination, $input->getOption('keep-uml'));
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Exception
     */
    private function ensureSourceIsReadable(InputInterface $input)
    {
        if (!is_readable($input->getArgument('source'))) {
            throw new \Exception('File/Directory does not exist or is not readable.');
        }
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Exception
     */
    private function ensureDestinationIsWritable(InputInterface $input)
    {
        if (!is_writable(dirname($input->getArgument('destination')))) {
            throw new \Exception('Destination is not writable.');
        }
    }
}
