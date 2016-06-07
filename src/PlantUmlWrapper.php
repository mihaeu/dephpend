<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;

class PlantUmlWrapper
{
    /** @var ShellWrapper */
    private $shell;

    /** @var PlantUmlFormatter */
    private $plantUmlFormatter;

    /**
     * @param PlantUmlFormatter $plantUmlFormatter
     * @param ShellWrapper      $shell
     *
     * @throws PlantUmlNotInstalledException
     */
    public function __construct(PlantUmlFormatter $plantUmlFormatter, ShellWrapper $shell)
    {
        $this->ensurePlantUmlIsInstalled($shell);

        $this->shell = $shell;
        $this->plantUmlFormatter = $plantUmlFormatter;
    }

    public function generate(ClazzDependencies $clazzDependencies, \SplFileInfo $destination, bool $keepUml = false)
    {
        $uml = $this->plantUmlFormatter->format($clazzDependencies);
        $umlDestination = preg_replace('/\.\w+$/', '.uml', $destination->getPathname());
        file_put_contents($umlDestination, $uml);
        $this->shell->run('plantuml '.$umlDestination);

        if ($keepUml === false) {
            unlink($umlDestination);
        }
    }

    /**
     * @throws PlantUmlNotInstalledException
     */
    private function ensurePlantUmlIsInstalled(ShellWrapper $shell)
    {
        if ($shell->run('plantuml -version') !== 0) {
            throw new PlantUmlNotInstalledException();
        }
    }
}
