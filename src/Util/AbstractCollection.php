<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use Closure;

/**
 * @template T
 *
 * @implements Collection<T>
 */
abstract class AbstractCollection implements Collection
{
    /** @var array<T> */
    protected $collection = [];

    /**
     * {@inheritdoc}
     */
    public function any(Closure $closure): bool
    {
        foreach ($this->collection as $item) {
            if ($closure($item) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Closure $closure
     *
     * @return bool
     */
    public function none(Closure $closure): bool
    {
        return !$this->any($closure);
    }

    /**
     * {@inheritdoc}
     */
    public function each(Closure $closure): void
    {
        foreach ($this->collection as $item) {
            $closure($item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapToArray(Closure $closure): array
    {
        return array_map($closure, $this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function reduce(mixed $initial, Closure $closure): mixed
    {
        return array_reduce($this->collection, $closure, $initial);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Closure $closure): Collection
    {
        $clone = clone $this;
        $clone->collection = array_values(array_filter($this->collection, $closure));

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->collection);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($other): bool
    {
        return in_array($other, $this->collection);
    }

    public function toString(): string
    {
        return implode(PHP_EOL, $this->mapToArray(function ($x) {
            return $x->toString();
        }));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(Collection $other): bool
    {
        return $this->toString() === $other->toString();
    }
}
