<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use InvalidArgumentException;
use Mihaeu\PhpDependencies\OS\MermaidWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MermaidCommand extends BaseCommand
{
    public function __construct(private MermaidWrapper $mermaidWrapper)
    {
        parent::__construct('mermaid');

        $this->defaultFormat = 'mmd';
        $this->allowedFormats = [$this->defaultFormat];
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Generate a Mermaid Class diagram of your dependencies')
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Destination for the generated class diagram (in .mmd format).'
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
        $this->mermaidWrapper->generate(
            $this->dependencies->reduceEachDependency($this->postProcessors),
            $destination,
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
