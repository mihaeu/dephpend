<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class ClazzCollection extends AbstractCollection
{
    /**
     * @param Clazz $clazz
     *
     * @return ClazzCollection
     */
    public function add(Clazz $clazz) : ClazzCollection
    {
        $clone = clone $this;
        $clone->collection[] = $clazz;

        return $clone;
    }
}
