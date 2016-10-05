<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\Analyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
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
     * @param string $name
     * @param PhpFileFinder $phpFileFinder
     * @param Parser $parser
     * @param Analyser $analyser
     * @throws LogicException
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
                'underscore-namespaces',
                'u',
                InputOption::VALUE_NONE,
                'Parse underscores in Class names as namespaces.'
            )
            ->addOption(
                'filter-namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Analyse only classes where both to and from are in this namespace.'
            )
            ->addOption(
                'filter-from',
                'f',
                InputOption::VALUE_REQUIRED,
                'Analyse only dependencies which originate from this namespace.'
            )
            ->addOption(
                'no-classes',
                null,
                InputOption::VALUE_NONE,
                'Remove all classes and analyse only namespaces.'
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
     * @throws \InvalidArgumentException
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
     *
     * @return DependencyMap
     */
    protected function detectDependencies(array $sources) : DependencyMap
    {
        return $this->analyser->analyse(
            $this->parser->parse($this->phpFileFinder->getAllPhpFilesFromSources($sources))
        );
    }

    /**
     * Pre-filters can safely be executed before every command as they only limit
     * the selection of dependencies. They do NOT change the dependencies themselves.
     *
     * @param DependencyMap $dependencies
     * @param string[] $options
     *
     * @return DependencyMap
     */
    protected function preFilterByInputOptions(DependencyMap $dependencies, array $options) : DependencyMap
    {
        if (!$options['internals']) {
            $dependencies = $dependencies->removeInternals();
        }

        if (isset($options['filter-from'])) {
            $dependencies = $dependencies->filterByFromNamespace($options['filter-from']);
        }

        return $dependencies;
    }

    /**
     * Post-filters should be applied after analysing commands which depend on information
     * that is more detailed than what is being displayed
     *
     * e.g. number of classes in a package would not work if the DependencyMap would
     * be reduced to package level before analysing.
     *
     * @param DependencyMap $dependencies
     * @param array $options
     *
     * @return DependencyMap
     */
    protected function postFilterByInputOptions(DependencyMap $dependencies, array $options) : DependencyMap
    {
        if ($options['depth'] > 0) {
            $dependencies = $dependencies->filterByDepth($options['depth']);
        }

        if ($options['filter-namespace']) {
            $dependencies = $dependencies->filterByNamespace($options['filter-namespace']);
        }

        if (isset($options['no-classes']) && $options['no-classes'] === true) {
            $dependencies = $dependencies->filterClasses();
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
