<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ErrorOutput
{
    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function writeln(string $message): void
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $io->getErrorStyle()->writeln($message);
    }
}