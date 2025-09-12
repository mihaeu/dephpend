<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Closure;
use InvalidArgumentException;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Util\Functional;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use function implode;
use function in_array;
use function is_readable;
use function is_writable;
use function preg_replace;

abstract class BaseCommand extends Command
{
    protected DependencyMap $dependencies;

    protected Closure $postProcessors;

    protected string $defaultFormat;

    /** @var list<string> */
    protected array $allowedFormats;

    public function __construct(?string $name = null)
    {
        $this->dependencies = new DependencyMap();
        $this->postProcessors = Functional::id();

        parent::__construct($name);
    }


    public function setDependencies(DependencyMap $dependencies): void
    {
        $this->dependencies = $dependencies;
    }

    public function setPostProcessors(Closure $postProcessors): void
    {
        $this->postProcessors = $postProcessors;
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
                null,
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
            ->addOption(
                'exclude-regex',
                'e',
                InputOption::VALUE_REQUIRED,
                'Exclude all dependencies which match the (PREG) regular expression.'
            )
            ->addOption(
                'dynamic',
                null,
                InputOption::VALUE_REQUIRED,
                'Adds dependency information from dynamically analysed function traces, for more information check out https://dephpend.com'
            )
        ;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function ensureOutputFormatIsValid(string $destination): void
    {
        if (!in_array(preg_replace('/.+\.(\w+)$/', '$1', $destination), $this->allowedFormats, true)) {
            throw new InvalidArgumentException('Output format is not allowed ('.implode(', ', $this->allowedFormats).')');
        }
    }

    /**
     * @param list<string> $sources
     *
     * @throws InvalidArgumentException
     */
    protected function ensureSourcesAreReadable(array $sources): void
    {
        foreach ($sources as $source) {
            if (!is_readable($source)) {
                throw new InvalidArgumentException('File/Directory does not exist or is not readable.');
            }
        }
    }

    /**
     * @param string $destination
     *
     * @throws InvalidArgumentException
     */
    protected function ensureDestinationIsWritable(string $destination): void
    {
        if (!is_writable(dirname($destination))) {
            throw new InvalidArgumentException('Destination is not writable.');
        }
    }
}
