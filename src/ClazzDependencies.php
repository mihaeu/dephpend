<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class ClazzDependencies extends AbstractCollection
{
    /**
     * @param Dependency $dependency
     *
     * @return ClazzDependencies
     */
    public function add(Dependency $dependency) : ClazzDependencies
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
