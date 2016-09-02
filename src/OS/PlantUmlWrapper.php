<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyPairCollection;
use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;
use Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter;
use Mihaeu\PhpDependencies\Util\Collection;

class PlantUmlWrapper
{
    /** @var ShellWrapper */
    private $shell;

    /** @var PlantUmlFormatter */
    private $plantUmlFormatter;

    /**
     * @param PlantUmlFormatter $plantUmlFormatter
     * @param ShellWrapper      $shell
     */
    public function __construct(PlantUmlFormatter $plantUmlFormatter, ShellWrapper $shell)
    {
        $this->shell = $shell;
        $this->plantUmlFormatter = $plantUmlFormatter;
    }

    /**
     * @param DependencyPairCollection $dependencyCollection
     * @param \SplFileInfo             $destination
     * @param bool                     $keepUml
     *
     * @throws PlantUmlNotInstalledException
     */
    public function generate(Collection $dependencyCollection, \SplFileInfo $destination, bool $keepUml = false)
    {
        $this->ensurePlantUmlIsInstalled($this->shell);

        $uml = $this->plantUmlFormatter->format($dependencyCollection);
        $umlDestination = preg_replace('/\.\w+$/', '.uml', $destination->getPathname());
        file_put_contents($umlDestination, str_replace('\\', '.', $uml));
        $this->shell->run('plantuml '.$umlDestination);

        if ($keepUml === false) {
            unlink($umlDestination);
        }
    }

    /**
     * @param ShellWrapper $shell
     *
     * @throws PlantUmlNotInstalledException
     */
    private function ensurePlantUmlIsInstalled(ShellWrapper $shell)
    {
        if ($shell->run('plantuml -version') !== 0) {
            throw new PlantUmlNotInstalledException();
        }
    }
}
