<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class DependencyStructureMatrixBuilder
{
    /**
     * @param DependencyPairCollection $dependencyPairCollection
     *
     * @return array
     */
    public function buildMatrix(DependencyPairCollection $dependencyPairCollection) : array
    {
        $dependencies = $dependencyPairCollection->allDependencies();
        $emptyDsm = $dependencies->reduce([], function (array $combined, Dependency $dependency) use ($dependencies) {
            $combined[$dependency->toString()] = array_combine(
                array_values($dependencies->toArray()),     // keys:    dependency name
                array_pad([], $dependencies->count(), 0)    // values:  [0, 0, 0, ... , 0]
            );

            return $combined;
        });

        return $dependencyPairCollection->reduce($emptyDsm, function (array $dsm, DependencyPair $dependency) use ($emptyDsm) {
            $dsm[$dependency->from()->toString()][$dependency->to()->toString()] += 1;

            return $dsm;
        });
    }
}
