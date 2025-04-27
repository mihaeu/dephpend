<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use InvalidArgumentException;
use Mihaeu\PhpDependencies\OS\PlantUmlWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UmlCommand extends BaseCommand
{
    public function __construct(private PlantUmlWrapper $plantUmlWrapper)
    {
        parent::__construct('uml');

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = $input->getOptions();
        $this->ensureSourcesAreReadable($input->getArgument('source'));
        $this->ensureOutputExists($options['output']);
        $this->ensureDestinationIsWritable($options['output']);
        $this->ensureOutputFormatIsValid($options['output']);

        $destination = new \SplFileInfo($options['output']);
        $this->plantUmlWrapper->generate(
            $this->dependencies->reduceEachDependency($this->postProcessors),
            $destination,
            $options['keep-uml'] ?? false
        );

        return 0;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureOutputExists(mixed $outputOption): void
    {
        if ($outputOption === null) {
            throw new InvalidArgumentException('Output not defined (use "help" for more information).');
        }
    }
}
