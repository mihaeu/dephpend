<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class PlantUmlFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyPairCollection $dependencyCollection) : string
    {
        return '@startuml'.PHP_EOL.$this->dependenciesInPlantUmlFormat($dependencyCollection).'@enduml';
    }

    /**
     * @param DependencyPairCollection $dependencyCollection
     *
     * @return mixed
     */
    private function dependenciesInPlantUmlFormat(DependencyPairCollection $dependencyCollection) : string
    {
        return $dependencyCollection->reduce('', function (string $output, DependencyPair $dependency) {
            return $output.$dependency->from()->toString().' --|> '.$dependency->to()->toString().PHP_EOL;
        });
    }
}
