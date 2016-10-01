<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

interface Collection extends \Countable
{
    /**
     * True if any element matches the $closure.
     *
     * @param \Closure $closure
     *
     * @return bool
     */
    public function any(\Closure $closure) : bool;

    /**
     * @param \Closure $closure
     *
     * @return bool
     */
    public function none(\Closure $closure) : bool;

    /**
     * Applies $closure to each element.
     *
     * @param \Closure $closure
     */
    public function each(\Closure $closure);

    /**
     * Returns a new array by applying the $closure to each element.
     *
     * @param \Closure $closure
     *
     * @return array
     */
    public function mapToArray(\Closure $closure) : array;

    /**
     * @param mixed    $initial
     * @param \Closure $closure
     *
     * @return mixed
     */
    public function reduce($initial, \Closure $closure);

    /**
     * @param \Closure $closure
     *
     * @return Collection
     */
    public function filter(\Closure $closure) : Collection;

    /**
     * @return array
     */
    public function toArray() : array;

    /**
     * @param $other
     *
     * @return bool
     */
    public function contains($other) : bool;

    /**
     * @return string
     */
    public function toString() : string;

    /**
     * @param Collection $other
     *
     * @return bool
     */
    public function equals(Collection $other) : bool;
}
