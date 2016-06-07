<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class ClazzDependencies implements \Countable
{
    /** Dependency[] */
    private $collection = [];

    /**
     * @param Dependency $dependency
     *
     * @return ClazzDependencies
     */
    public function addDependency(Dependency $dependency) : ClazzDependencies
    {
        $dependencyCollection = new self();
        $dependencyCollection->collection = $this->collection;
        $dependencyCollection->collection[$dependency->toString()] = $dependency;

        return $dependencyCollection;
    }

    /**
     * @param Clazz $clazz
     *
     * @return ClazzCollection
     */
    public function classesDependingOn(Clazz $clazz) : ClazzCollection
    {
        $clazzCollection = new ClazzCollection();
        foreach ($this->collection as $dependency) {
            /** @var Dependency $dependency */
            if ($dependency->from()->equals($clazz)) {
                $clazzCollection = $clazzCollection->add($dependency->to());
            }
        }

        return $clazzCollection;
    }

    /**
     * Applies $closure to each pair of dependencies.
     *
     * @param \Closure $closure with argument Dependency $dependency
     */
    public function each(\Closure $closure)
    {
        foreach ($this->collection as $item) {
            $closure($item);
        }
    }

    /**
     * @param mixed    $initial
     * @param \Closure $closure
     *
     * @return mixed
     */
    public function reduce($initial, \Closure $closure)
    {
        return array_reduce($this->collection, $closure, $initial);
    }

    /**
     * Count elements of an object.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     *
     * @since 5.1.0
     */
    public function count() : int
    {
        return count($this->collection);
    }
}
