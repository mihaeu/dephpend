<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Formatters;

use Closure;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;

class DependencyStructureMatrixBuilder
{
    /**
     * @return array<string, array<string, int>>
     */
    public function buildMatrix(DependencyMap $dependencies, Closure $mappers): array
    {
        $emptyDsm = $this->createEmptyDsm($dependencies->mapAllDependencies($mappers));
        return $dependencies->reduce($emptyDsm, function (array $dsm, Dependency $from, Dependency $to) use ($mappers): array {
            $from = $mappers($from)->toString();
            $to = $mappers($to)->toString();
            $dsm[$to][$from] += 1;
            return $dsm;
        });
    }

    /**
     * @return array<string, array<string, int>>
     */
    private function createEmptyDsm(DependencySet $dependencies): array
    {
        return $dependencies->reduce([], function (array $combined, Dependency $dependency) use ($dependencies) {
            $combined[$dependency->toString()] = array_combine(
                array_values(array_map(static fn (Dependency $d): string => $d->toString(), $dependencies->toArray())),     // keys:    dependency name
                array_pad([], $dependencies->count(), 0)    // values:  [0, 0, 0, ... , 0]
            );
            return $combined;
        });
    }
}
