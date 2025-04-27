<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Util;

use Closure;
use Countable;

/**
 * @template T
 */
interface Collection extends Countable
{
    /**
     * True if any element matches the $closure.
     */
    public function any(Closure $closure): bool;

    public function none(Closure $closure): bool;

    /**
     * Applies $closure to each element.
     */
    public function each(Closure $closure): void;

    /**
     * Returns a new array by applying the $closure to each element.
     *
     * @return array<int|string, mixed>
     */
    public function mapToArray(Closure $closure): array;

    public function reduce(mixed $initial, Closure $closure): mixed;

    /**
     * @return Collection<T>
     */
    public function filter(Closure $closure): Collection;

    /**
     * @return array<T>
     */
    public function toArray(): array;

    /**
     * @param T $other
     */
    public function contains($other): bool;

    public function toString(): string;

    /**
     * @param Collection<T> $other
     */
    public function equals(Collection $other): bool;

    public function count(): int;

    public function isEmpty(): bool;
}
