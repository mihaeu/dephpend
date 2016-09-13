<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TextCommand extends BaseCommand
{
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser)
    {
        parent::__construct('text', $phpFileFinder, $parser, $analyser);
    }

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Generate a Dependency Structure Matrix of your dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureSourcesAreReadable($input->getArgument('source'));

        $dependencies = $this->detectDependencies($input->getArgument('source'));
        $options = $input->getOptions();
        $output->writeln(
            $this->filterByInputOptions($dependencies, $options)
                ->filterByDepth((int) $options['depth'])
                ->toString()
        );
    }
}
