<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
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
     * @param StaticAnalyser $analyser
     * @param DependencyFilter $dependencyFilter
     * @param PlantUmlWrapper $plantUmlWrapper
     *
     */
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        StaticAnalyser $analyser,
        DependencyFilter $dependencyFilter,
        PlantUmlWrapper $plantUmlWrapper
    ) {
        parent::__construct('uml', $phpFileFinder, $parser, $analyser, $dependencyFilter);

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

        $mappers = $this->dependencyFilter->postFiltersByOptions($options);
        $dependencies = $this->dependencyFilter->filterByOptions(
            $this->detectDependencies($input->getArgument('source')),
            $options
        )->reduce(new DependencyMap(), function (DependencyMap $map, Dependency $from, Dependency $to) use ($mappers) {
            return $map->add(
                $mappers($from),
                $mappers($to)
            );
        });
        $destination = new \SplFileInfo($options['output']);
        $this->plantUmlWrapper->generate(
            $dependencies,
            $destination,
            $options['keep-uml']
        );
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
