<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyPairCollection;

class PlantUmlFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyPairCollection $dependencyCollection) : string
    {
        return '@startuml'.PHP_EOL.$this->dependenciesInPlantUmlFormat($dependencyCollection).PHP_EOL.'@enduml';
    }

    /**
     * @param DependencyPairCollection $dependencyCollection
     *
     * @return mixed
     */
    private function dependenciesInPlantUmlFormat(DependencyPairCollection $dependencyCollection) : string
    {
        return str_replace('-->', '--|>', $dependencyCollection->toString());
    }
}
