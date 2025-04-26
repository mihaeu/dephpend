<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Dependencies\Dependency;
use Mihaeu\PhpDependencies\Util\AbstractCollection;

/**
 * @extends AbstractCollection<Dependency>
 */
class DependencySet extends AbstractCollection
{
    /**
     * @param Dependency $dependency
     */
    public function add($dependency): self
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
