<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class PlantUmlFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyCollection $dependencyCollection) : string
    {
        return '@startuml'.PHP_EOL.$this->dependenciesInPlantUmlFormat($dependencyCollection).'@enduml';
    }

    /**
     * @param DependencyCollection $dependencyCollection
     *
     * @return mixed
     */
    private function dependenciesInPlantUmlFormat(DependencyCollection $dependencyCollection) : string
    {
        return $dependencyCollection->reduce('', function (string $output, Dependency $dependency) {
            return $output.$dependency->from()->toString().' --|> '.$dependency->to()->toString().PHP_EOL;
        });
    }
}
