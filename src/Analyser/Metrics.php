<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Analyser;

use Mihaeu\PhpDependencies\Dependencies\AbstractClazz;
use Mihaeu\PhpDependencies\Dependencies\Clazz;
use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Dependencies\DependencyPair;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\Dependencies\DependencySet;
use Mihaeu\PhpDependencies\Dependencies\Interfaze;
use Mihaeu\PhpDependencies\Dependencies\Trait_;

class Metrics
{
    /**
     * @param DependencyMap $map
     *
     * @return float Value from 0 (completely concrete) to 1 (completely abstract)
     */
    public function abstractness(DependencyMap $map) : float
    {
        $abstractions = $this->abstractClassCount($map)
            + $this->interfaceCount($map)
            + $this->traitCount($map);
        $allClasses = $this->classCount($map)
            + $this->abstractClassCount($map)
            + $this->interfaceCount($map)
            + $this->traitCount($map);
        return $abstractions / $allClasses;
    }

    public function classCount(DependencyMap $map) : int
    {
        return $this->countFilteredItems($map, function (Dependency $dependency) {
            return $dependency instanceof Clazz;
        });
    }

    public function abstractClassCount(DependencyMap $map) : int
    {
        return $this->countFilteredItems($map, function (Dependency $dependency) {
            return $dependency instanceof AbstractClazz;
        });
    }

    public function interfaceCount(DependencyMap $map) : int
    {
        return $this->countFilteredItems($map, function (Dependency $dependency) {
            return $dependency instanceof Interfaze;
        });
    }

    public function traitCount(DependencyMap $map) : int
    {
        return $this->countFilteredItems($map, function (Dependency $dependency) {
            return $dependency instanceof Trait_;
        });
    }

    /**
     * Afferent coupling is an indicator for the responsibility of a package.
     *
     * @param DependencyMap $map
     *
     * @return array
     */
    public function afferentCoupling(DependencyMap $map) : array
    {
        return $map->reduce([], function (array $afferent, DependencySet $to, Dependency $from) use ($map) {
            $afferent[$from->toString()] = 0;
            return $map->reduce($afferent, function (array $afferent, DependencySet $to, Dependency $fromOther) use ($from) {
                if ($to->any(function (Dependency $dependency) use ($from) {
                    return $dependency->equals($from);
                })) {
                    ++$afferent[$from->toString()];
                }
                return $afferent;
            });
        });
    }

    /**
     * Efferent coupling is an indicator for how independent a package is.
     *
     * @param DependencyMap $map
     *
     * @return array
     */
    public function efferentCoupling(DependencyMap $map) : array
    {
        return $map->reduce([], function (array $efferent, DependencySet $to, Dependency $from) {
            $efferent[$from->toString()] = $to->count();
            return $efferent;
        });
    }

    /**
     * Instability is an indicator for how resilient a package is towards change.
     *
     * @param DependencyMap $map
     *
     * @return array Key: Class Value: Range from 0 (completely stable) to 1 (completely unstable)
     */
    public function instability(DependencyMap $map) : array
    {
        $ce = $this->efferentCoupling($map);
        $ca = $this->afferentCoupling($map);
        $instability = [];
        foreach ($ce as $class => $count) {
            $totalCoupling = $ce[$class] + $ca[$class];
            $instability[$class] = $totalCoupling !== 0
                ? $ce[$class] / $totalCoupling
                : 0;
        }
        return $instability;
    }

    private function countFilteredItems(DependencyMap $map, \Closure $closure)
    {
        return $map->fromDependencies()->filter($closure)->count();
    }
}
