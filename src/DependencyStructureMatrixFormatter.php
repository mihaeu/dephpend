<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

abstract class DependencyStructureMatrixFormatter implements Formatter
{
    /*
     * {@inheritdoc}
     */
    abstract public function format(DependencyPairCollection $dependencyCollection) : string;

    /**
     * @param DependencyPairCollection $dependencyPairCollection
     * @param DependencyCollection     $dependencyCollection
     *
     * @return array
     */
    protected function buildMatrix(DependencyPairCollection $dependencyPairCollection, DependencyCollection $dependencyCollection) : array
    {
        $emptyDsm = $dependencyCollection->reduce([], function (array $combined, Dependency $dependency) use ($dependencyCollection) {
            $combined[$dependency->toString()] = array_combine(array_values($dependencyCollection->toArray()), array_pad([], $dependencyCollection->count(), 0));

            return $combined;
        });

        return $dependencyPairCollection->reduce($emptyDsm, function (array $dsm, DependencyPair $dependency) use ($emptyDsm) {
            $dsm[$dependency->from()->toString()][$dependency->to()->toString()] += 1;

            return $dsm;
        });
    }
}
