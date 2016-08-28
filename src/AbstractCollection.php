<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies;

abstract class AbstractCollection implements Collection
{
    /** @var array */
    protected $collection = [];

    /**
     * {@inheritdoc}
     */
    public function any(\Closure $closure) : bool
    {
        foreach ($this->collection as $item) {
            if ($closure($item) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function each(\Closure $closure)
    {
        foreach ($this->collection as $item) {
            $closure($item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapToArray(\Closure $closure) : array
    {
        return array_map($closure, $this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function reduce($initial, \Closure $closure)
    {
        return array_reduce($this->collection, $closure, $initial);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(\Closure $closure) : Collection
    {
        $clone = clone $this;
        $clone->collection = array_values(array_filter($this->collection, $closure));

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($other) : bool
    {
        return in_array($other, $this->collection);
    }

    public function toString() : string
    {
        return implode(PHP_EOL, $this->mapToArray(function ($x) {
            return $x->toString();
        }));
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
}
