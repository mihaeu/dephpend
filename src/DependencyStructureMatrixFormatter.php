<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

abstract class DependencyStructureMatrixFormatter implements Formatter
{
    /*
     * {@inheritdoc}
     */
    abstract public function format(DependencyPairCollection $dependencyCollection) : string;

    /**
     * @param DependencyPairCollection $dependencyCollection
     * @param ClazzCollection          $clazzCollection
     *
     * @return array
     */
    protected function buildMatrix(DependencyPairCollection $dependencyCollection, ClazzCollection $clazzCollection) : array
    {
        $emptyDsm = $clazzCollection->reduce([], function (array $combined, Clazz $clazz) use ($clazzCollection) {
            $combined[$clazz->toString()] = array_combine(array_values($clazzCollection->toArray()), array_pad([], $clazzCollection->count(), 0));

            return $combined;
        });

        return $dependencyCollection->reduce($emptyDsm, function (array $dsm, DependencyPair $dependency) use ($emptyDsm) {
            $dsm[$dependency->from()->toString()][$dependency->to()->toString()] += 1;

            return $dsm;
        });
    }
}
