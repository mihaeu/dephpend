<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

abstract class AbstractMap implements Collection
{
    /** @var array */
    protected $map = [];

    protected static $KEY = 'key';
    protected static $VALUE = 'value';

    /**
     * @inheritDoc
     */
    public function any(\Closure $closure) : bool
    {
        foreach ($this->map as $item) {
            if ($closure($item[self::$VALUE], $item[self::$KEY]) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function none(\Closure $closure) : bool
    {
        return !$this->any($closure);
    }

    /**
     * @inheritDoc
     */
    public function each(\Closure $closure)
    {
        foreach ($this->map as $item) {
            $closure($item[self::$VALUE], $item[self::$KEY]);
        }
    }

    /**
     * @inheritDoc
     */
    public function mapToArray(\Closure $closure) : array
    {
        $xs = [];
        foreach ($this->map as $item) {
            $xs[] = $closure($item[self::$VALUE], $item[self::$KEY]);
        }
        return $xs;
    }

    /**
     * @inheritDoc
     */
    public function reduce($initial, \Closure $closure)
    {
        foreach ($this->map as $key => $item) {
            $initial = $closure($initial, $item[self::$VALUE], $item[self::$KEY]);
        }
        return $initial;
    }

    /**
     * @inheritDoc
     */
    public function filter(\Closure $closure) : Collection
    {
        $clone = clone $this;
        $clone->map = [];
        foreach ($this->map as $key => $item) {
            if ($closure($item[self::$VALUE], $item[self::$KEY]) === true) {
                $clone->map[$key] = $item;
            }
        }
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function toArray() : array
    {
        return $this->map;
    }

    /**
     * @inheritDoc
     */
    public function contains($other) : bool
    {
        return $this->any(function ($value, $key) use ($other) {
            return $key instanceof $other && $key->equals($other);
        });
    }

    /**
     * @inheritDoc
     */
    abstract public function toString() : string;

    /**
     * @inheritDoc
     */
    public function equals(Collection $other) : bool
    {
        return $this instanceof $other && $this->toString() === $other->toString();
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->map);
    }
}
