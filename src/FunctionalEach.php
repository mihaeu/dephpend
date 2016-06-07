<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

trait FunctionalEach
{
    /**
     * Applies $closure to each element.
     *
     * @param \Closure $closure
     */
    public function each(\Closure $closure)
    {
        foreach ($this->collection as $item) {
            $closure($item);
        }
    }
}
