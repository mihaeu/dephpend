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

    /**
     * Afferent coupling is an indicator for the responsibility of a package.
     *
     * @param DependencyPairCollection $dependencies
     *
     * @return array
     */
    public function afferentCoupling(DependencyPairCollection $dependencies) : array
    {
        $afferent = [];
        foreach ($this->extractFromDependencies($dependencies)->toArray() as $dependencyFrom) {
            /** @var Dependency $dependencyFrom */
            $afferent[$dependencyFrom->toString()] = 0;

            foreach ($dependencies->toArray() as $dependencyPair) {
                /** @var DependencyPair $dependencyPair */
                if ($dependencyPair->to()->equals($dependencyFrom)) {
                    ++$afferent[$dependencyFrom->toString()];
                }
            }
        }
        return $afferent;
    }

    /**
     * Efferent coupling is an indicator for how independent a package is.
     *
     * @param DependencyPairCollection $dependencies
     *
     * @return array
     */
    public function efferentCoupling(DependencyPairCollection $dependencies) : array
    {
        $efferent = [];
        foreach ($this->extractFromDependencies($dependencies)->toArray() as $dependencyFrom) {
            /** @var Dependency $dependencyFrom */
            $efferent[$dependencyFrom->toString()] = 0;

            foreach ($dependencies->toArray() as $dependencyPair) {
                /** @var DependencyPair $dependencyPair */
                if ($dependencyPair->from()->equals($dependencyFrom)) {
                    $efferent[$dependencyFrom->toString()] += 1;
                }
            }
        }
        return $efferent;
    }

    private function extractFromDependencies(DependencyPairCollection $dependencyPairCollection) : DependencyCollection
    {
        return $dependencyPairCollection->reduce(new DependencyCollection(), function (DependencyCollection $dependencies, DependencyPair $dependencyPair) {
            if (!$dependencies->contains($dependencyPair->from())) {
                $dependencies = $dependencies->add($dependencyPair->from());
            }
            return $dependencies;
        });
    }

    private function countFilteredItems(DependencyPairCollection $dependencyPairCollection, \Closure $closure)
    {
        return $this->extractFromDependencies($dependencyPairCollection)->filter($closure)->count();
    }
}
