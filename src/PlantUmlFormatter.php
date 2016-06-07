<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class PlantUmlFormatter
{
    /**
     * @param ClazzDependencies $clazzDependencies
     *
     * @return string
     */
    public function format(ClazzDependencies $clazzDependencies) : string
    {
        return '@startuml'.PHP_EOL.$this->dependenciesInPlantUmlFormat($clazzDependencies).'@enduml';
    }

    /**
     * @param ClazzDependencies $clazzDependencies
     *
     * @return mixed
     */
    private function dependenciesInPlantUmlFormat(ClazzDependencies $clazzDependencies) : string
    {
        return $clazzDependencies->reduce('', function (string $output, Dependency $dependency) {
            return $output.$dependency->from()->toString().' --|> '.$dependency->to()->toString().PHP_EOL;
        });
    }
}
