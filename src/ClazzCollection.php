<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class ClazzCollection
{
    use FunctionalEach;

    /** @var Clazz[] */
    private $collection = [];

    /**
     * @param Clazz $clazz
     *
     * @return ClazzCollection
     */
    public function add(Clazz $clazz) : ClazzCollection
    {
        $collection = new self();
        $collection->collection = $this->collection;
        $collection->collection[] = $clazz;

        return $collection;
    }

    /**
     * @return Clazz[]
     */
    public function toArray() : array
    {
        return $this->collection;
    }
}
