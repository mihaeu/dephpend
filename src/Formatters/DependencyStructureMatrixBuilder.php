<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;

class DependencyStructureMatrixBuilder
{
    /**
     * @param DependencyMap $map
     * @param int $depth
     *
     * @return array
     */
    public function buildMatrix(DependencyMap $map, int $depth = 0) : array
    {
        $initial = $this->createEmptyDsm($map->allDependencies()->reduceToDepth($depth));
        return $map->reduce($initial, function (array $dsm, Dependency $from, Dependency $to) use ($depth) {
            $dsm[$from->reduceToDepth($depth)->toString()][$to->reduceToDepth($depth)->toString()] += 1;
            return $dsm;
        });
    }

    /**
     * @param $dependencies
     *
     * @return array
     */
    private function createEmptyDsm(DependencySet $dependencies)
    {
        return $dependencies->reduce([], function (array $combined, Dependency $dependency) use ($dependencies) {
            $combined[$dependency->toString()] = array_combine(
                array_values($dependencies->toArray()),     // keys:    dependency name
                array_pad([], $dependencies->count(), 0)    // values:  [0, 0, 0, ... , 0]
            );
            return $combined;
        });
    }
}
