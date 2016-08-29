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
        $fromDependencies = $dependencyPairCollection->fromDependencies();
        $emptyDsm = $fromDependencies->reduce([], function (array $combined, Dependency $dependency) use ($fromDependencies) {
            $combined[$dependency->toString()] = array_combine(
                array_values($fromDependencies->toArray()),     // keys:    dependency name
                array_pad([], $fromDependencies->count(), 0)    // values:  [0, 0, 0, ... , 0]
            );

            return $combined;
        });

        return $dependencyPairCollection->reduce($emptyDsm, function (array $dsm, DependencyPair $dependency) use ($emptyDsm) {
            $dsm[$dependency->from()->toString()][$dependency->to()->toString()] += 1;

            return $dsm;
        });
    }
}
