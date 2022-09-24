<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Symfony\Component\Console\Style\SymfonyStyle;

class ErrorOutput
{
    /** @var SymfonyStyle */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function writeln(string $message): void
    {
        $this->symfonyStyle->getErrorStyle()->writeln($message);
    }
}
