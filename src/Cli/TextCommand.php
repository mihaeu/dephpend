<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Dependency;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->ensureSourceIsReadable($input->getArgument('source'));

        $source = new \SplFileInfo($input->getArgument('source'));
        $this->detectDependencies($source, $input->getOption('internals'), $input->getOption('only-namespaces'))
            ->each(function (Dependency $dependency) use ($output) {
            $output->writeln($dependency->toString());
        });
    }
}
