<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
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
        $dependencies = $this->allDependenciesReducedByDepth($map, $depth);
        $emptyDsm = $this->createEmptyDsm($dependencies);

        return $map->reduce($emptyDsm, function (array $dsm, DependencySet $to, Dependency $from) use ($depth) {
            $fromKey = $from->reduceToDepth($depth)->toString();
            return $to->reduceToDepth($depth)->reduce($dsm, function (array $dsm, Dependency $to) use ($fromKey) {
                $dsm[$fromKey][$to->toString()] += 1;
                return $dsm;
            });
        });
    }

    /**
     * @param DependencyMap $dependencyPairCollection
     *
     * @param int $depth
     * @return DependencySet
     */
    private function allDependenciesReducedByDepth(DependencyMap $dependencyPairCollection, int $depth)
    {
        return $dependencyPairCollection->allDependencies()->reduce(new DependencySet(),
            function (DependencySet $dependencyCollection, Dependency $dependency) use ($depth) {
                return $dependencyCollection->add($dependency->reduceToDepth($depth));
            }
        );
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
