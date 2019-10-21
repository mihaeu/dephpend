<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\DotNotInstalledException;
use Mihaeu\PhpDependencies\Formatters\DotFormatter;

class DotWrapper
{
    /** @var ShellWrapper */
    private $shellWrapper;

    /** @var DotFormatter */
    private $dotFormatter;

    /**
     * @param DotFormatter $dotFormatter
     * @param ShellWrapper $shellWrapper
     */
    public function __construct(DotFormatter $dotFormatter, ShellWrapper $shellWrapper)
    {
        $this->dotFormatter = $dotFormatter;
        $this->shellWrapper = $shellWrapper;
    }

    public function generate(DependencyMap $dependencies, \SplFileInfo $destination, bool $keepDotFile = false)
    {
        $this->ensureDotIsInstalled();

        $dotFile = new \SplFileInfo($destination->getPath()
            .'/'.$destination->getBasename('.'.$destination->getExtension()));
        file_put_contents($dotFile->getPathname(), $this->dotFormatter->format($dependencies));

        if ('dot' !== $destination->getExtension()) {
            $this->shellWrapper->run('dot -O -T'.$destination->getExtension().' '.$dotFile->getPathname());
            if ($keepDotFile === false) {
                unlink($dotFile->getPathname());
            }
        }
    }

    private function ensureDotIsInstalled()
    {
        if ($this->shellWrapper->run('dot -V') !== 0) {
            throw new DotNotInstalledException();
        }
    }
}
