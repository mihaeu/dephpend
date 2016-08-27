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
        $classCount = $this->classCount($dependencies);
        $abstractClassCount = $this->abstractClassCount($dependencies);
        $interfaceCount = $this->interfaceCount($dependencies);
        $traitCount = $this->traitCount($dependencies);

        $abstractions = $abstractClassCount + $interfaceCount + $traitCount;
        $allClasses = $classCount + $abstractClassCount + $interfaceCount + $traitCount;

        return [
            'classes'               => $classCount,
            'abstractClasses'       => $abstractClassCount,
            'interfaces'            => $interfaceCount,
            'traits'                => $traitCount,
            'abstractness'          => $abstractions / $allClasses,
        ];
    }

    private function countFilteredItems(DependencyPairCollection $dependencyPairCollection, \Closure $closure)
    {
        return $dependencyPairCollection->reduce(new DependencyCollection(), function (DependencyCollection $dependencies, DependencyPair $dependencyPair) {
            if (!$dependencies->contains($dependencyPair->from())) {
                $dependencies = $dependencies->add($dependencyPair->from());
            }
            if (!$dependencies->contains($dependencyPair->to())) {
                $dependencies = $dependencies->add($dependencyPair->to());
            }
            return $dependencies;
        })->filter($closure)->count();
    }

    private function classCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof Clazz;
        });
    }

    private function abstractClassCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof AbstractClazz;
        });
    }

    private function interfaceCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof Interfaze;
        });
    }

    private function traitCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof Trait_;
        });
    }
}
