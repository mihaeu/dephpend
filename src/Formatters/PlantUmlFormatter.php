<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\DependencyMap;

class PlantUmlFormatter implements Formatter
{
    /**
     * {@inheritdoc}
     */
    public function format(DependencyMap $map) : string
    {
        return '@startuml'.PHP_EOL.$this->dependenciesInPlantUmlFormat($map).PHP_EOL.'@enduml';
    }

    /**
     * @param DependencyMap $map
     *
     * @return mixed
     */
    private function dependenciesInPlantUmlFormat(DependencyMap $map) : string
    {
        return str_replace('-->', '--|>', $map->toString());
    }
}
