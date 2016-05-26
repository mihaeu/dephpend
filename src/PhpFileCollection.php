<?php declare(strict_types = 1);

namespace mihaeu\phpDependencies;

use Traversable;

class PhpFileCollection implements \Countable, \IteratorAggregate
{
    /** @var PhpFile[] */
    private $collection = [];

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
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->collection);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    }
}
