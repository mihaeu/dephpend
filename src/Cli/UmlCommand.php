<?php

declare(strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\DependencyInspectionVisitor;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Mihaeu\PhpDependencies\PlantUmlFormatter;
use Mihaeu\PhpDependencies\PlantUmlWrapper;
use Mihaeu\PhpDependencies\ShellWrapper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UmlCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('uml')
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
        $this->ensureSourceIsReadable($input, $output);
        $this->ensureDestinationIsWritable($input, $output);

        $parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
        $files = (new PhpFileFinder())->find(new \SplFileInfo($input->getArgument('source')));
        $ast = $parser->parse($files);
        $dependencies = (new Analyser(
            new NodeTraverser(),
            new DependencyInspectionVisitor())
        )->analyse($ast);

        $plantUml = new PlantUmlWrapper(new PlantUmlFormatter(), new ShellWrapper());
        $destination = new \SplFileInfo($input->getArgument('destination'));
        $plantUml->generate($dependencies, $destination, $input->getOption('keep-uml'));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function ensureSourceIsReadable(
        InputInterface $input,
        OutputInterface $output
    ) {
        if (!is_readable($input->getArgument('source'))) {
            throw new \Exception('File/Directory does not exist or is not readable.');
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function ensureDestinationIsWritable(
        InputInterface $input,
        OutputInterface $output
    ) {
        if (!is_writable(dirname($input->getArgument('destination')))) {
            throw new \Exception('Destination is not writable.');
        }
    }
}
