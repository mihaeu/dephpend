<?php

declare(strict_types=1);

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
        $clone->collection[] = $dependency;

        return $clone;
    }
}
