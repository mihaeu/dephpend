<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyPairSet;

class PlantUmlFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyPairSet $dependencyCollection) : string
    {
        return '@startuml'.PHP_EOL.$this->dependenciesInPlantUmlFormat($dependencyCollection).PHP_EOL.'@enduml';
    }

    /**
     * @param DependencyPairSet $dependencyCollection
     *
     * @return mixed
     */
    private function dependenciesInPlantUmlFormat(DependencyPairSet $dependencyCollection) : string
    {
        return str_replace('-->', '--|>', $dependencyCollection->toString());
    }
}
