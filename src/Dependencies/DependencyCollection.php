<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\AbstractCollection;

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
