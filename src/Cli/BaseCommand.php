<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser;
use Mihaeu\PhpDependencies\DependencyPairCollection;
use Mihaeu\PhpDependencies\Parser;
use Mihaeu\PhpDependencies\PhpFileCollection;
use Mihaeu\PhpDependencies\PhpFileFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class BaseCommand extends Command
{
    /** @var PhpFileFinder */
    protected $phpFileFinder;

    /** @var Parser */
    protected $parser;

    /** @var Analyser */
    protected $analyser;

    /** @var string */
    protected $defaultFormat;

    /** @var string[] */
    protected $allowedFormats;

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

    protected function configure()
    {
        $this
            ->addArgument(
                'source',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Location of your PHP source files.'
            )
            ->addOption(
                'internals',
                null,
                InputOption::VALUE_NONE,
                'Check for dependencies from internal PHP Classes like SplFileInfo.'
            )
            ->addOption(
                'depth',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Output dependencies as packages instead of single classes.',
                0
            )
            ->addOption(
                'memory',
                'm',
                InputOption::VALUE_REQUIRED,
                'Set maximum memory e.g. 2048M'
            )
            ->addOption(
                'underscore-namespaces',
                'u',
                InputOption::VALUE_NONE,
                'Parse underscores in Class names as namespaces.'
            )
            ->addOption(
                'filter-namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Analyse only classes from this namespace'
            )
        ;
    }

    /**
     * @param string $destination
     *
     * @throws \Exception
     */
    protected function ensureOutputFormatIsValid(string $destination)
    {
        if (!in_array(preg_replace('/.+\.(\w+)$/', '$1', $destination), $this->allowedFormats, true)) {
            throw new \InvalidArgumentException('Output format is not allowed ('.implode(', ', $this->allowedFormats).')');
        }
    }

    /**
     * @param string[] $sources
     *
     * @throws \Exception
     */
    protected function ensureSourcesAreReadable(array $sources)
    {
        foreach ($sources as $source) {
            if (!is_readable($source)) {
                throw new \InvalidArgumentException('File/Directory does not exist or is not readable.');
            }
        }
    }

    /**
     * @param string[] $sources
     * @param bool $withInternals
     * @param int $depth
     * @param string $vendor
     *
     * @return DependencyPairCollection
     */
    protected function detectDependencies(array $sources, bool $withInternals = false, int $depth = 0, string $vendor = null) : DependencyPairCollection
    {
        $files = array_reduce($sources, function (PhpFileCollection $collection, string $source) {
            return $collection->addAll($this->phpFileFinder->find(new \SplFileInfo($source)));
        }, new PhpFileCollection());

        return $this->analyser->analyse(
            $this->parser->parse($files)
        );
    }

    /**
     * @param DependencyPairCollection $dependencies
     * @param string[] $options
     *
     * @return DependencyPairCollection
     */
    protected function filterByInputOptions(DependencyPairCollection $dependencies, array $options) : DependencyPairCollection
    {
        if ($options['internals']) {
            $dependencies = $dependencies->removeInternals();
        }

        if ($options['filter-namespace']) {
            $dependencies = $dependencies->filterByNamespace($options['filter-namespace']);
        }

        if ($options['depth']) {
            $dependencies = $dependencies->filterByDepth((int) $options['depth']);
        }

        return $dependencies;
    }

    /**
     * @param string $destination
     *
     * @throws \Exception
     */
    protected function ensureDestinationIsWritable(string $destination)
    {
        if (!is_writable(dirname($destination))) {
            throw new \InvalidArgumentException('Destination is not writable.');
        }
    }
}
