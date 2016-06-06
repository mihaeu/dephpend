<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use Mihaeu\PhpDependencies\Exceptions\PlantUmlNotInstalledException;

class PlantUmlWrapper
{
    /** @var ShellWrapper */
    private $shell;

    /**
     * @param ShellWrapper $shell
     *
     * @throws PlantUmlNotInstalledException
     */
    public function __construct(ShellWrapper $shell)
    {
        $this->ensurePlantUmlIsInstalled($shell);

        $this->shell = $shell;
    }

    public function generate(array $clazzDependencies, \SplFileInfo $destination, bool $keepUml = false)
    {
        $output = [];
        foreach ($clazzDependencies as $clazzName => $clazzDependency) {
            /* @var ClazzDependencies $clazzDependency */
            $arrow = $clazzDependency->count() > 2
                ? ' -up-|> '
                : ' -down-|> ';
            foreach ($clazzDependency->dependencies() as $clazz) {
                /* @var Clazz $clazz */
                $output[] = $clazzName.$arrow.$clazz->toString();
            }
        }
        $umlDestination = preg_replace('/\.\w+$/', '.uml', $destination->getPathname());
        file_put_contents($umlDestination, '@startuml'.PHP_EOL.implode(PHP_EOL, $output).PHP_EOL.'@enduml');
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
