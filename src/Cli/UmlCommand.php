<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UmlCommand extends BaseCommand
{
    /** @var PlantUmlWrapper */
    private $plantUmlWrapper;

    /**
     * @param PhpFileFinder $phpFileFinder
     * @param Parser $parser
     * @param Analyser $analyser
     * @param PlantUmlWrapper $plantUmlWrapper
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
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
        $options = $input->getOptions();
        $this->ensureSourcesAreReadable($input->getArgument('source'));
        $this->ensureOutputExists($options['output']);
        $this->ensureDestinationIsWritable($options['output']);
        $this->ensureOutputFormatIsValid($options['output']);

        $dependencies = $this->postFilterByInputOptions(
            $this->preFilterByInputOptions(
                $this->detectDependencies($input->getArgument('source')),
                $options
            ),
            $options
        );
        $destination = new \SplFileInfo($options['output']);
        $this->plantUmlWrapper->generate($dependencies, $destination, $options['keep-uml']);
    }

    /**
     * @param $outputOption
     *
     * @throws \InvalidArgumentException
     */
    private function ensureOutputExists($outputOption)
    {
        if ($outputOption === null) {
            throw new \InvalidArgumentException('Output not defined (use "help" for more information).');
        }
    }
}
