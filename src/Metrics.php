<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class Metrics
{
    /**
     * @param DependencyPairCollection $dependencies
     *
     * @return array
     */
    public function computeMetrics(DependencyPairCollection $dependencies) : array
    {
        return [
            'classes' => $this->classCount($dependencies),
            'interfaces' => $this->interfaceCount($dependencies),
            'traits' => $this->traitCount($dependencies),
        ];
    }

    private function classCount(DependencyPairCollection $dependencies) : int
    {
        return count($dependencies->reduce([], function (array $total, DependencyPair $dependencyPair) {
            if ($dependencyPair->from() instanceof Clazz) {
                $total[$dependencyPair->from()->toString()] = 1;
            }

            if ($dependencyPair->to() instanceof Clazz) {
                $total[$dependencyPair->to()->toString()] = 1;
            }
            return $total;
        }));
    }

    private function interfaceCount(DependencyPairCollection $dependencies) : int
    {
        return count($dependencies->reduce([], function (array $total, DependencyPair $dependencyPair) {
            if ($dependencyPair->from() instanceof Interfaze) {
                $total[$dependencyPair->from()->toString()] = 1;
            }

            if ($dependencyPair->to() instanceof Interfaze) {
                $total[$dependencyPair->to()->toString()] = 1;
            }
            return $total;
        }));
    }

    private function traitCount(DependencyPairCollection $dependencies) : int
    {
        return count($dependencies->reduce([], function (array $total, DependencyPair $dependencyPair) {
            if ($dependencyPair->from() instanceof Trait_) {
                $total[$dependencyPair->from()->toString()] = 1;
            }

            if ($dependencyPair->to() instanceof Trait_) {
                $total[$dependencyPair->to()->toString()] = 1;
            }
            return $total;
        }));
    }
}
