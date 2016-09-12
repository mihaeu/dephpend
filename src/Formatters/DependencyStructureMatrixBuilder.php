<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyPairSet;

class DependencyStructureMatrixBuilder
{
    /**
     * @param DependencyPairSet $dependencyPairCollection
     * @param int $depth
     *
     * @return array
     */
    public function buildMatrix(DependencyPairSet $dependencyPairCollection, int $depth = 0) : array
    {
        $dependencies = $this->allDependenciesReducedByDepth($dependencyPairCollection, $depth);
        $emptyDsm = $this->createEmptyDsm($dependencies);

        return $dependencyPairCollection->reduce($emptyDsm, function (array $dsm, DependencyPair $dependency) use ($depth) {
            $from = $dependency->from()->reduceToDepth($depth)->toString();
            $to = $dependency->to()->reduceToDepth($depth)->toString();
            $dsm[$from][$to] += 1;

            return $dsm;
        });
    }

    /**
     * @param DependencyPairSet $dependencyPairCollection
     *
     * @param int $depth
     * @return DependencySet
     */
    private function allDependenciesReducedByDepth(DependencyPairSet $dependencyPairCollection, int $depth)
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
    private function createEmptyDsm($dependencies)
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
