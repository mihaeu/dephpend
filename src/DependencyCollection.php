<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class DependencyCollection extends AbstractCollection
{
    /**
     * @param Dependency $dependency
     *
     * @return DependencyCollection
     */
    public function add(Dependency $dependency) : DependencyCollection
    {
        $clone = clone $this;
        if (in_array($dependency, $this->collection)) {
            return $clone;
        }

        $clone->collection[] = $dependency;
        return $clone;
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
}
