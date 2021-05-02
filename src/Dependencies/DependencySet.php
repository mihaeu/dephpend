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
        if ($this->contains($dependency)
            || $dependency->count() === 0) {
            return $clone;
        }

        $clone->collection[] = $dependency;
        return $clone;
    }
}
