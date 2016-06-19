<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    /** @var PhpFileFinder */
    protected $phpFileFinder;

    /** @var Parser */
    protected $parser;

    /** @var Analyser */
    protected $analyser;

    /**
     * @param string        $name
     * @param PhpFileFinder $phpFileFinder
     * @param Parser        $parser
     * @param Analyser      $analyser
     */
    public function __construct(
        string $name,
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        Analyser $analyser
    ) {
        parent::__construct($name);

        $this->phpFileFinder = $phpFileFinder;
        $this->parser = $parser;
        $this->analyser = $analyser;
    }

    /**
     * @param string $source
     *
     * @throws \Exception
     */
    protected function ensureSourceIsReadable(string $source)
    {
        if (!is_readable($source)) {
            throw new \Exception('File/Directory does not exist or is not readable.');
        }
    }

    /**
     * @param string $destination
     *
     * @throws \Exception
     */
    protected function ensureDestinationIsWritable(string $destination)
    {
        if (!is_writable(dirname($destination))) {
            throw new \Exception('Destination is not writable.');
        }
    }
}
