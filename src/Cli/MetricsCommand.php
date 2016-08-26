<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;

class MetricsCommand extends BaseCommand
{
    public function __construct(
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser
    ) {
        parent::__construct('metrics', $phpFileFinder, $parser, $analyser);
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Generate dependency metrics')
        ;
    }
}
