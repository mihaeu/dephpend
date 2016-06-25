<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DsmCommand extends BaseCommand
{
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser)
    {
        parent::__construct('dsm', $phpFileFinder, $parser, $analyser);

        $this->defaultFormat = 'html';
        $this->allowedFormats = [$this->defaultFormat];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
