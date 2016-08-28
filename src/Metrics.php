<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class Metrics
{
    public function abstractness(DependencyPairCollection $dependencies) : float
    {
        $abstractions = $this->abstractClassCount($dependencies)
            + $this->interfaceCount($dependencies)
            + $this->traitCount($dependencies);
        $allClasses = $this->classCount($dependencies)
            + $this->abstractClassCount($dependencies)
            + $this->interfaceCount($dependencies)
            + $this->traitCount($dependencies);
        return $abstractions / $allClasses;
    }

    public function classCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof Clazz;
        });
    }

    public function abstractClassCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof AbstractClazz;
        });
    }

    public function interfaceCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof Interfaze;
        });
    }

    public function traitCount(DependencyPairCollection $dependencies) : int
    {
        return $this->countFilteredItems($dependencies, function (Dependency $dependency) {
            return $dependency instanceof Trait_;
        });
    }

    private function countFilteredItems(DependencyPairCollection $dependencyPairCollection, \Closure $closure)
    {
        return $dependencyPairCollection->reduce(new DependencyCollection(), function (DependencyCollection $dependencies, DependencyPair $dependencyPair) {
            if (!$dependencies->contains($dependencyPair->from())) {
                $dependencies = $dependencies->add($dependencyPair->from());
            }
            return $dependencies;
        })->filter($closure)->count();
    }
}
