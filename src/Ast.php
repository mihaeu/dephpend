<?php

declare (strict_types = 1);

namespace Mihaeu\PhpDependencies;

use PhpParser\Node;

class Ast implements \Iterator
{
    /** @var \SplObjectStorage */
    private $nodes;

    /**
     * Ast constructor.
     */
    public function __construct()
    {
        $this->nodes = new \SplObjectStorage();
    }

    /**
     * @param PhpFile $file
     * @param Node[]  $node
     */
    public function add(PhpFile $file, array $node)
    {
        $this->nodes->attach($file, $node);
    }

    /**
     * Return the current element.
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     *
     * @since 5.0.0
     */
    public function current()
    {
        return $this->nodes->current();
    }

    /**
     * Move forward to next element.
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @since 5.0.0
     */
    public function next()
    {
        $this->nodes->next();
    }

    /**
     * Return the key of the current element.
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     *
     * @since 5.0.0
     */
    public function key()
    {
        return $this->nodes->key();
    }

    /**
     * Checks if current position is valid.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     *
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->nodes->valid();
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->nodes->rewind();
    }

    public function get(PhpFile $file)
    {
        return $this->nodes->contains($file)
            ? $this->nodes[$file]
            : null;
    }
}
