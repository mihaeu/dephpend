<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;

class DependencyStructureMatrixBuilder
{
    /**
     * @param DependencyMap $all
     * @param DependencyMap $filtered
     *
     * @return array
     */
    public function buildMatrix(DependencyMap $all, DependencyMap $filtered) : array
    {
        $emptyDsm = $this->createEmptyDsm($filtered->allDependencies());
        return $all->reduce($emptyDsm, function (array $dsm, Dependency $from, Dependency $to) use ($filtered) {
            $filtered->each(function (Dependency $filteredFrom, Dependency $filteredTo) use (&$dsm, $from, $to) {
                if ($this->inPackage($filteredFrom, $from) && $this->inPackage($filteredTo, $to)) {
                    $dsm[$filteredFrom->toString()][$filteredTo->toString()] += 1;
                }
            });
            return $dsm;
        });
    }

    private function inPackage(Dependency $filtered, Dependency $original) : bool
    {
        return $filtered->namespaze()->count() === 0 && $original->namespaze()->count() === 0
            ? $filtered->equals($original)
            : $filtered->inNamespaze($original->namespaze());
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
