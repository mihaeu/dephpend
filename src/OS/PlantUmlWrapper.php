<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\OS;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;
use Mihaeu\PhpDependencies\Formatters\PlantUmlFormatter;
use SplFileInfo;

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
     * @throws PlantUmlNotInstalledException
     */
    public function generate(DependencyMap $map, SplFileInfo $destination, bool $keepUml = false): void
    {
        $this->ensurePlantUmlIsInstalled($this->shell);

        $umlDestination = preg_replace('/\.\w+$/', '.uml', $destination->getPathname());
        file_put_contents($umlDestination, $this->plantUmlFormatter->format($map));
        $this->shell->run('plantuml ' . $umlDestination);

        if ($keepUml === false) {
            unlink($umlDestination);
        }
    }

    /**
     * @throws PlantUmlNotInstalledException
     */
    private function ensurePlantUmlIsInstalled(ShellWrapper $shell): void
    {
        if ($shell->run('plantuml -version') !== 0) {
            throw new PlantUmlNotInstalledException();
        }
    }
}
