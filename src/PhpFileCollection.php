<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use Traversable;

class PhpFileCollection implements \Countable, \IteratorAggregate
{
    /** @var PhpFile[] */
    private $collection = [];

    /**
     * Shorthand for creating a collection with one element in just one line.
     *
     * @param PhpFile $file
     */
    public function __construct(PhpFile $file = null)
    {
        if ($file !== null) {
            $this->add($file);
        }
    }

    public function add(PhpFile $file)
    {
        $this->collection[] = $file;
    }

    public function get(int $i) : PhpFile
    {
        if (!array_key_exists($i, $this->collection)) {
            throw new IndexOutOfBoundsException();
        }

        return $this->collection[$i];
    }

    public function equals(PhpFileCollection $other) : bool
    {
        return $this->collection === $other->collection;
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

    /**
     * Retrieve an external iterator.
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }

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
}
