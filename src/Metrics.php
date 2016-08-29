<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

class Metrics
{
    /**
     * @param DependencyPairCollection $dependencies
     *
     * @return float Value from 0 (completely concrete) to 1 (completely abstract)
     */
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
        foreach ($dependencies->fromDependencies()->toArray() as $dependencyFrom) {
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
        foreach ($dependencies->fromDependencies()->toArray() as $dependencyFrom) {
            /** @var Dependency $dependencyFrom */
            $efferent[$dependencyFrom->toString()] = 0;
            foreach ($dependencies->toArray() as $dependencyPair) {
                /** @var DependencyPair $dependencyPair */
                if ($dependencyPair->from()->equals($dependencyFrom)
                ) {
                    ++$efferent[$dependencyFrom->toString()];
                }
            }
        }
        return $efferent;
    }

    /**
     * Instability is an indicator for how resilient a package is towards change.
     *
     * @param DependencyPairCollection $dependencyPairCollection
     *
     * @return array Key: Class Value: Range from 0 (completely stable) to 1 (completely unstable)
     */
    public function instability(DependencyPairCollection $dependencyPairCollection) : array
    {
        $ce = $this->efferentCoupling($dependencyPairCollection);
        $ca = $this->afferentCoupling($dependencyPairCollection);
        $instability = [];
        foreach ($ce as $class => $count) {
            $totalCoupling = $ce[$class] + $ca[$class];
            $instability[$class] = $totalCoupling !== 0
                ? $ce[$class] / $totalCoupling
                : 0;
        }
        return $instability;
    }

    private function countFilteredItems(DependencyPairCollection $dependencyPairCollection, \Closure $closure)
    {
        return $dependencyPairCollection->fromDependencies()->filter($closure)->count();
    }
}
