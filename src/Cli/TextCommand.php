<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TextCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct('text');
    }

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Generate a Dependency Structure Matrix of your dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureSourcesAreReadable($input->getArgument('source'));

        $output->writeln($this->dependencies->reduceEachDependency($this->postProcessors)->toString());
    }
}
