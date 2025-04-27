<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use Closure;

/**
 * @implements Collection<TValue>
 * @template TKey
 * @template TValue
 */
abstract class AbstractMap implements Collection
{
    /** @var array<TKey, TValue> */
    protected $map = [];

    protected const KEY = 'key';
    protected const VALUE = 'value';

    /**
     * @inheritDoc
     */
    public function any(Closure $closure): bool
    {
        foreach ($this->map as $item) {
            foreach ($item[self::VALUE]->toArray() as $subItem) {
                if ($closure($item[self::KEY], $subItem) === true) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function none(Closure $closure): bool
    {
        return !$this->any($closure);
    }

    /**
     * @inheritDoc
     */
    public function each(Closure $closure): void
    {
        foreach ($this->map as $item) {
            foreach ($item[self::VALUE]->toArray() as $subItem) {
                $closure($item[self::KEY], $subItem);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function mapToArray(Closure $closure): array
    {
        $xs = [];
        foreach ($this->map as $item) {
            foreach ($item[self::VALUE]->toArray() as $subItem) {
                $xs[] = $closure($item[self::KEY], $subItem);
            }
        }
        return $xs;
    }

    /**
     * @inheritDoc
     */
    public function reduce(mixed $initial, Closure $closure): mixed
    {
        foreach ($this->map as $key => $item) {
            foreach ($item[self::VALUE]->toArray() as $subItem) {
                $initial = $closure($initial, $item[self::KEY], $subItem);
            }
        }
        return $initial;
    }

    /**
     * @inheritDoc
     */
    public function filter(Closure $closure): Collection
    {
        $clone = clone $this;
        $clone->map = [];
        foreach ($this->map as $key => $item) {
            foreach ($item[self::VALUE]->toArray() as $subItem) {
                if ($closure($item[self::KEY], $subItem) === true) {
                    $clone = $clone->add($item[self::KEY], $subItem);
                }
            }
        }
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->map;
    }

    /**
     * @inheritDoc
     */
    public function contains($other): bool
    {
        foreach ($this->map as $key => $item) {
            if ($item[self::KEY] instanceof $other && $item[self::KEY]->equals($other)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    abstract public function toString(): string;

    /**
     * Adds an item to the map.
     *
     * @param TKey $key
     * @param TValue $value
     * @return static
     */
    abstract public function add($key, $value): static;

    /**
     * @inheritDoc
     */
    public function equals(Collection $other): bool
    {
        return $this instanceof $other
            && $this->toString() === $other->toString();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->map);
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
