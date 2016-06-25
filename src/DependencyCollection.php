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
    public function findClassesDependingOn(Clazz $clazz) : ClazzCollection
    {
        return $this->filter(function (Dependency $dependency) use ($clazz) {
            return $dependency->from()->equals($clazz);
        })->reduce(new ClazzCollection(), function (ClazzCollection $clazzCollection, Dependency $dependency) {
            return $clazzCollection->add($dependency->to());
        });
    }

    /**
     * @return ClazzCollection
     */
    public function allClasses() : ClazzCollection
    {
        return $this->reduce(new ClazzCollection(), function (ClazzCollection $clazzes, Dependency $dependency) {
            if (!$clazzes->contains($dependency->from())) {
                $clazzes = $clazzes->add($dependency->from());
            }
            if (!$clazzes->contains($dependency->to())) {
                $clazzes = $clazzes->add($dependency->to());
            }

            return $clazzes;
        });
    }
}
