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
        $dependencies = $dependencyPairCollection
            ->allDependencies()
            ->reduce(new DependencyCollection(), function (DependencyCollection $dependencyCollection, Dependency $dependency) {
                return $dependencyCollection->add($dependency->reduceToDepth(2));
            }
        );
        $emptyDsm = $dependencies->reduce([], function (array $combined, Dependency $dependency) use ($dependencies) {
            $combined[$dependency->toString()] = array_combine(
                array_values($dependencies->toArray()),     // keys:    dependency name
                array_pad([], $dependencies->count(), 0)    // values:  [0, 0, 0, ... , 0]
            );

            return $combined;
        });

        return $dependencyPairCollection->reduce($emptyDsm, function (array $dsm, DependencyPair $dependency) {
            $dsm[$dependency->from()->reduceToDepth(2)->toString()][$dependency->to()->reduceToDepth(2)->toString()] += 1;

            return $dsm;
        });
    }
}
