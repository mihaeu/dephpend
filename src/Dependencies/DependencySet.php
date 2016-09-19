<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\AbstractCollection;

class DependencySet extends AbstractCollection
{
    /**
     * @param Dependency $dependency
     *
     * @return DependencySet
     */
    public function add(Dependency $dependency) : DependencySet
    {
        $clone = clone $this;
        if ($this->contains($dependency)) {
            return $clone;
        }

        $clone->collection[] = $dependency;
        return $clone;
    }

    public function addAll(DependencySet $otherSet) : DependencySet
    {
        return $otherSet->reduce($this, function (DependencySet $set, Dependency $dependency) {
            return $set->add($dependency);
        });
    }

    public function reduceToDepth(int $depth) : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $dependency) use ($depth) {
            return $set->add($dependency->reduceToDepth($depth));
        });
    }

    public function reduceDepthFromLeftBy(int $reduction) : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $dependency) use ($reduction) {
            return $set->add($dependency->reduceDepthFromLeftBy($reduction));
        });
    }
}
