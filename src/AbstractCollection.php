<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

class AbstractCollection implements \Countable
{
    /** @var array */
    protected $collection = [];

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

    /**
     * Returns a new array by applying the $closure to each element.
     *
     * @param \Closure $closure
     *
     * @return array
     */
    public function mapToArray(\Closure $closure) : array
    {
        return array_map($closure, $this->collection);
    }

    /**
     * @param mixed    $initial
     * @param \Closure $closure
     *
     * @return mixed
     */
    public function reduce($initial, \Closure $closure)
    {
        return array_reduce($this->collection, $closure, $initial);
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->collection;
    }

    /**
     * Count elements of an object.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     *
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->collection);
    }
}
