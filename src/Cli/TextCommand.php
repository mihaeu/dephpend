<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'text',
    description: 'Prints a list of all dependencies',
    hidden: false,
)]
class TextCommand extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureSourcesAreReadable($input->getArgument('source'));

        $output->writeln($this->dependencies->reduceEachDependency($this->postProcessors)->toString());

        return 0;
    }
}
